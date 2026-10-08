<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ApiMiddleware;
use App\Http\Middleware\RoleMiddleWare;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(ApiMiddleware::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            // 'dynamic.database' => DynamicDatabase::class,
        ]);

        // API has no login page: don't redirect guests (route('login') doesn't exist)
        $middleware->redirectGuestsTo(fn ($request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always answer API errors (e.g. missing/expired token -> 401) as JSON,
        // even when the client doesn't send "Accept: application/json"
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());
    })->create();

