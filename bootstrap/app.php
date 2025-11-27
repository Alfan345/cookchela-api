<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add middleware alias
        $middleware->alias([
            'force. json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Apply to API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle Authentication Exception
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'errors' => null,
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ],
                ], 401);
            }
        });

        // Handle Validation Exception
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ],
                ], 422);
            }
        });

        // Handle Not Found Exception
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint tidak ditemukan',
                    'errors' => null,
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ],
                ], 404);
            }
        });

        // Handle Method Not Allowed Exception
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method tidak diizinkan',
                    'errors' => null,
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                    ],
                ], 405);
            }
        });
    })->create();