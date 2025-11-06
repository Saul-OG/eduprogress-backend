<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Datos inválidos',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Recurso no encontrado'], 404);
            }
        });

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage() ?: 'Error HTTP'], $e->getStatusCode());
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => config('app.debug') ? $e->getMessage() : 'Server error',
                ], 500);
            }
        });
    }
}
