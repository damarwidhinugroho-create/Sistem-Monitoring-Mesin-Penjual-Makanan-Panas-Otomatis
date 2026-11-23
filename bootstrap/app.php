<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if ($exception instanceof AuthenticationException) {
                return null;
            }

            if (! $request->expectsJson()) {
                $status = $exception instanceof HttpExceptionInterface
                    ? $exception->getStatusCode()
                    : 500;

                $views = [
                    403 => 'errors.403',
                    404 => 'errors.404',
                    500 => 'errors.500',
                    503 => 'errors.503',
                ];

                if (isset($views[$status])) {
                    return response()->view($views[$status], [], $status);
                }

                if ($status >= 500) {
                    return response()->view('errors.500', [], 500);
                }

                return response()->view('errors.generic', [], $status);
            }
        });
    })->create();
