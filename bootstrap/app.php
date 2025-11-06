<?php

use App\Http\Middleware\ForceJsonResponse;
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
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
      $middleware->group('api', [
          ForceJsonResponse::class,
      ]);
})

    ->withExceptions(function (Exceptions $exceptions) {
        // Puedes personalizar el render aquí si quieres
    })
    ->create();
