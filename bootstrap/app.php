<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS is handled automatically for API routes in Laravel
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'banned' => \App\Http\Middleware\CheckBannedUser::class,
        ]);
        
        // Sanctum handles API authentication via tokens, no need for sessions/CSRF on API routes
        
        // Check banned users on web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckBannedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
