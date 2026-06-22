<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web', 'auth:web']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.admin.https' => \App\Http\Middleware\EnsureAdminHttps::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
            'customer.session' => \App\Http\Middleware\EnsureCustomerGuardAuthenticated::class,
            'account.guest' => \App\Http\Middleware\RedirectStaffFromAccount::class,
            'admin.guest' => \App\Http\Middleware\RedirectStaffFromAdminLogin::class,
        ]);
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if ($request->user(\App\Support\AuthGuards::CUSTOMER)) {
                return route('account.dashboard');
            }

            $staff = $request->user(\App\Support\AuthGuards::STAFF);

            if ($staff?->hasAnyRole(['Admin', 'Kitchen'])) {
                return route('admin.dashboard');
            }

            return route('home');
        });
        $middleware->web(append: [
            \App\Http\Middleware\SetActiveTheme::class,
            \App\Http\Middleware\ApplyBroadcastingConfig::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface::class,
        ]);

        $exceptions->report(function (\Throwable $e): void {
            app(\App\Services\CriticalErrorReporter::class)->report($e);
        });

        $exceptions->render(fn (\Throwable $e, \Illuminate\Http\Request $request) => app(\App\Exceptions\ExceptionRenderer::class)->render($e, $request));
    })->create();
