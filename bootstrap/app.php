<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\EnsureAdminAccess::class,
            'two-factor' => \App\Http\Middleware\EnsureTwoFactorVerified::class,
        ]);
        // EasyPanel terminates TLS; enable only for the configured private proxy network.
        $proxies = env('TRUSTED_PROXIES');
        if (is_string($proxies) && trim($proxies) !== '') {
            $middleware->trustProxies(
                at: trim($proxies) === '*' ? '*' : array_filter(array_map('trim', explode(',', $proxies))),
                headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                    | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                    | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
