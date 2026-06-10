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
        ['middleware' => ['web', 'auth']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.admin.https' => \App\Http\Middleware\EnsureAdminHttps::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetActiveTheme::class,
            \App\Http\Middleware\ApplyBroadcastingConfig::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('admin/notifications*', 'admin/push-subscriptions', 'admin/push-subscriptions/*', 'admin/settings/test-pusher', 'broadcasting/auth')
                && $request->expectsJson()) {
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException
                    || $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
                    || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                    return \App\Http\Responses\ApiResponse::error(
                        __('You don\'t have permission to do that.'),
                        403
                    );
                }

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return \App\Http\Responses\ApiResponse::error(
                        __('Your session expired. Please sign in again.'),
                        401
                    );
                }

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return \App\Http\Responses\ApiResponse::validation(
                        $e->errors(),
                        __('Validation failed.')
                    );
                }

                report($e);

                return \App\Http\Responses\ApiResponse::error(
                    __('Something went wrong. You can keep working — please try again.'),
                    500
                );
            }

            return null;
        });
    })->create();
