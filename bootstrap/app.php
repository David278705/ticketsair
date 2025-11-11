<?php

// Load Heroku configuration
if (file_exists(__DIR__ . '/../config/heroku.php')) {
    require __DIR__ . '/../config/heroku.php';
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // <- debe estar arriba

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Heroku proxies
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Enable CORS for web routes as well
        $middleware->web(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();

