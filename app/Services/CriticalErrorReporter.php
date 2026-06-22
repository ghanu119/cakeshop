<?php

namespace App\Services;

use App\Jobs\SendCriticalErrorMail;
use App\Mail\CriticalErrorNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use PDOException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class CriticalErrorReporter
{
    public function report(Throwable $e): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if (! $this->isCritical($e)) {
            return;
        }

        if ($this->isReportingLoop($e) ) {
            return;
        }

        if (! $this->acquireThrottle($e) && false) {
            return;
        }

        $payload = $this->buildPayload($e);

        try {
            SendCriticalErrorMail::dispatch($payload);
        } catch (Throwable $dispatchException) {
            try {
                Mail::to(config('error-reporting.recipient'))
                    ->send(new CriticalErrorNotification($payload));
            } catch (Throwable) {
                // Avoid recursive reporting; default logger already has the original exception.
            }
        }
    }

    public function isCritical(Throwable $e): bool
    {
        if ($this->isExcluded($e)) {
            return false;
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return true;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return false;
        }

        $request = $this->resolveRequest();

        if ($request === null) {
            return false;
        }

        return $this->resolveStatus($e) >= 500;
    }

    protected function isEnabled(): bool
    {
        if (! config('error-reporting.enabled')) {
            return false;
        }

        $recipient = config('error-reporting.recipient');

        return is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isExcluded(Throwable $e): bool
    {
        return match (true) {
            $e instanceof TokenMismatchException,
            $e instanceof AuthenticationException,
            $e instanceof ValidationException,
            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException,
            $e instanceof UnauthorizedException,
            $e instanceof NotFoundHttpException,
            $e instanceof ModelNotFoundException,
            $e instanceof TooManyRequestsHttpException,
            $e instanceof HttpExceptionInterface,
            $e instanceof MissingAppKeyException,
            $e instanceof MethodNotAllowedHttpException,
            $e instanceof TransportExceptionInterface => true,
            default => false,
        };
    }

    protected function isReportingLoop(Throwable $e): bool
    {
        if (str_contains($e::class, 'SendCriticalErrorMail')
            || str_contains($e::class, 'CriticalErrorNotification')) {
            return true;
        }

        $message = $e->getMessage();

        return str_contains($message, 'CriticalErrorNotification')
            || str_contains($message, 'SendCriticalErrorMail');
    }

    protected function acquireThrottle(Throwable $e): bool
    {
        $minutes = max(1, (int) config('error-reporting.throttle_minutes', 15));
        $key = 'critical-error:'.sha1($e::class.'|'.$e->getFile().'|'.$e->getLine().'|'.$e->getMessage());

        return Cache::add($key, true, now()->addMinutes($minutes));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(Throwable $e): array
    {
        $request = $this->resolveRequest();

        return [
            'exception_class' => $e::class,
            'exception_short_class' => class_basename($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'environment' => config('app.env'),
            'occurred_at' => now()->toIso8601String(),
            'request' => $this->buildRequestContext($request),
            'trace' => $this->buildTrace($e),
        ];
    }

  /**
     * @return array<string, mixed>|null
     */
    protected function buildRequestContext(?Request $request): ?array
    {
        if ($request === null) {
            return null;
        }

        $userContext = null;

        foreach (['web', 'customer'] as $guard) {
            $user = $request->user($guard);

            if ($user !== null) {
                $userContext = [
                    'guard' => $guard,
                    'id' => $user->getAuthIdentifier(),
                ];

                break;
            }
        }

        return [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user' => $userContext,
        ];
    }

    protected function buildTrace(Throwable $e): string
    {
        $frames = array_slice($e->getTrace(), 0, 20);
        $lines = [];

        foreach ($frames as $index => $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? 0;
            $class = $frame['class'] ?? '';
            $type = $frame['type'] ?? '';
            $function = $frame['function'] ?? '';

            $callable = $class !== '' ? $class.$type.$function : $function;
            $lines[] = sprintf('#%d %s(%s): %s()', $index, $file, $line, $callable);
        }

        return implode("\n", $lines);
    }

    protected function resolveRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }

    protected function resolveStatus(Throwable $e): int
    {
        if ($e instanceof TokenMismatchException) {
            return 419;
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof ValidationException) {
            return 422;
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return 429;
        }

        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof AuthorizationException
            || $e instanceof AccessDeniedHttpException
            || $e instanceof UnauthorizedException) {
            return 403;
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return 500;
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }
}
