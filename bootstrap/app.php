<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            // Hanya untuk API; validasi (422) memakai format bawaan Laravel
            if (! $request->is('api/*') || $e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                $message = match (true) {
                    $status === 403 => 'You do not have permission to perform this action.',
                    $status === 404 => 'Resource not found.',
                    $status === 405 => 'Method not allowed.',
                    $status === 429 => 'Too many requests.',
                    $status >= 500 => 'Server error.',
                    default => $e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error'),
                };

                return response()->json(['message' => $message], $status, $e->getHeaders());
            }

            // Error tak terduga: jangan bocorkan detail apa pun
            return response()->json(['message' => 'Server error.'], 500);
        });
    })->create();