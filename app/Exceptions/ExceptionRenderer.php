<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PDOException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ExceptionRenderer
{
    public function render(Throwable $e, Request $request): ?Response
    {
        $status = $this->resolveStatus($e);

        if (config('app.debug') && ! $this->wantsJsonResponse($request)) {
            if ($status >= 500 && ! $e instanceof HttpExceptionInterface) {
                return null;
            }
        }

        $message = $this->resolveMessage($e, $status);
        $code = $this->resolveCode($e, $status);

        if ($this->wantsJsonResponse($request)) {
            return $this->renderJson($e, $status, $message, $code);
        }

        return $this->renderHtml($e, $request, $status, $message);
    }

    protected function wantsJsonResponse(Request $request): bool
    {
        if ($request->expectsJson() || $request->ajax()) {
            return true;
        }

        return $request->is('admin/*', 'broadcasting/auth')
            && Str::contains((string) $request->header('Accept'), 'application/json');
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

    protected function resolveMessage(Throwable $e, int $status): string
    {
        if ($e instanceof TokenMismatchException) {
            return __('errors.csrf_expired');
        }

        if ($e instanceof AuthenticationException) {
            return __('errors.session_expired');
        }

        if ($e instanceof ValidationException) {
            return __('errors.validation_failed');
        }

        if ($e instanceof AuthorizationException
            || $e instanceof AccessDeniedHttpException
            || $e instanceof UnauthorizedException) {
            return __('errors.forbidden');
        }

        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            return __('errors.not_found');
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return __('errors.too_many_requests');
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return __('errors.database');
        }

        if ($e instanceof HttpExceptionInterface) {
            return match ($status) {
                503 => __('errors.maintenance'),
                404 => __('errors.not_found'),
                403 => __('errors.forbidden'),
                419 => __('errors.csrf_expired'),
                429 => __('errors.too_many_requests'),
                default => __('errors.server'),
            };
        }

        return __('errors.server');
    }

    protected function resolveCode(Throwable $e, int $status): ?string
    {
        return match (true) {
            $e instanceof TokenMismatchException => 'csrf_expired',
            $e instanceof AuthenticationException => 'session_expired',
            $e instanceof ValidationException => 'validation_failed',
            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException,
            $e instanceof UnauthorizedException => 'forbidden',
            $e instanceof NotFoundHttpException, $e instanceof ModelNotFoundException => 'not_found',
            $e instanceof TooManyRequestsHttpException => 'too_many_requests',
            $e instanceof QueryException, $e instanceof PDOException => 'database',
            $e instanceof HttpExceptionInterface && $status === 503 => 'maintenance',
            $e instanceof HttpExceptionInterface && $status === 419 => 'csrf_expired',
            default => $status >= 500 ? 'server_error' : null,
        };
    }

    protected function renderJson(Throwable $e, int $status, string $message, ?string $code): Response
    {
        if ($e instanceof ValidationException) {
            return ApiResponse::validation($e->errors(), $message);
        }

        if ($status >= 500 || $e instanceof QueryException || $e instanceof PDOException) {
            report($e);
        }

        return ApiResponse::error($message, $status, $code ? ['code' => $code] : null, $code);
    }

    protected function renderHtml(Throwable $e, Request $request, int $status, string $message): ?Response
    {
        if ($e instanceof ValidationException) {
            return null;
        }

        if ($this->isSessionExpiredOnForm($e, $status) && $this->isFormSubmission($request)) {
            return $this->redirectFormWithError($request, $message);
        }

        if ($e instanceof AuthenticationException) {
            $loginRoute = $request->is('admin', 'admin/*')
                ? route('admin.login')
                : route('login');

            return redirect()->guest($loginRoute)->withErrors(['_form' => $message]);
        }

        if ($status === 403 && $this->isFormSubmission($request)) {
            return $this->redirectFormWithError($request, $message);
        }

        if ($status === 429 && $this->isFormSubmission($request)) {
            return $this->redirectFormWithError($request, $message);
        }

        if ($this->isFormSubmission($request) && ($status >= 500 || $e instanceof QueryException || $e instanceof PDOException)) {
            report($e);

            return $this->redirectFormWithError($request, $message);
        }

        if ($status >= 500 || $e instanceof QueryException || $e instanceof PDOException) {
            report($e);
        }

        if ($this->hasErrorView($status)) {
            return $this->errorView($request, $status, $message);
        }

        return response($message, $status);
    }

    protected function isSessionExpiredOnForm(Throwable $e, int $status): bool
    {
        return $e instanceof TokenMismatchException
            || ($status === 419 && $e instanceof HttpExceptionInterface);
    }

    protected function isFormSubmission(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    protected function redirectFormWithError(Request $request, string $message): RedirectResponse
    {
        $guessed = $this->guessFormUrlFromRequest($request);

        $target = $request->is('account', 'account/*')
            ? ($guessed ?? session()->previousUrl() ?? $request->headers->get('referer'))
            : ($request->headers->get('referer') ?? session()->previousUrl() ?? $guessed);

        if (! $target || $target === $request->fullUrl()) {
            $target = $this->defaultFormUrl($request);
        }

        return redirect()->to($target)
            ->withInput($request->except('_token', 'password', 'password_confirmation', 'current_password'))
            ->withErrors(['_form' => $message]);
    }

    protected function guessFormUrlFromRequest(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        if ($path === '') {
            return null;
        }

        if ($request->is('account/login', 'account/verify-otp', 'account/register', 'account/register/*')) {
            return route('home', ['auth' => 1]);
        }

        if (in_array($request->method(), ['PUT', 'PATCH'], true)
            && preg_match('#^(admin/[^/]+)/(\d+)$#', $path, $matches)) {
            return url($matches[1].'/'.$matches[2].'/edit');
        }

        if ($request->isMethod('POST') && preg_match('#^admin/([^/]+)$#', $path, $matches)) {
            return url('admin/'.$matches[1].'/create');
        }

        return url($path);
    }

    protected function defaultFormUrl(Request $request): string
    {
        if ($request->is('admin/login')) {
            return route('admin.login');
        }

        if ($request->is('account/login', 'account/verify-otp', 'account/register', 'account/register/*')) {
            return route('home', ['auth' => 1]);
        }

        if ($request->is('account', 'account/*')) {
            return route('home', ['auth' => 1]);
        }

        if ($request->is('order/*')) {
            return route('home');
        }

        if ($request->is('contact', 'contact/*')) {
            return route('contact.index');
        }

        if ($request->is('admin', 'admin/*')) {
            return route('admin.dashboard');
        }

        if ($request->is('login')) {
            return route('login');
        }

        return route('home');
    }

    protected function hasErrorView(int $status): bool
    {
        return in_array($status, [403, 404, 419, 429, 500, 503], true);
    }

    protected function errorView(Request $request, int $status, string $message): Response
    {
        $view = "errors.{$status}";

        return response()->view($view, [
            'message' => $message,
            'status' => $status,
            'isAdmin' => $request->is('admin', 'admin/*'),
        ], $status);
    }
}
