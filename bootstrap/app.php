<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Guest;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => Guest::class,
            'admin' => IsAdmin::class
        ]);
        $middleware->validateCsrfTokens(except: [
            '/payment/callback'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
