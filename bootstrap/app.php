<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SOAP dipanggil aplikasi eksternal dan tidak mengirim token CSRF browser.
        $middleware->validateCsrfTokens(except: ['soap/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $exception): bool => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof AuthenticationException => 401,
                $exception instanceof ValidationException => 422,
                $exception instanceof ModelNotFoundException,
                $exception instanceof NotFoundHttpException => 404,
                default => null,
            };

            if ($status === null) {
                return null;
            }

            $title = match ($status) {
                401 => 'Unauthorized',
                404 => 'Not Found',
                default => 'Unprocessable Content',
            };

            $detail = match ($status) {
                401 => 'Autentikasi Bearer Token diperlukan atau token tidak valid.',
                404 => 'Rute atau data yang diminta tidak ditemukan.',
                default => 'Satu atau beberapa atribut yang dikirim tidak valid.',
            };

            $body = [
                'type' => 'about:blank',
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
                'instance' => $request->getPathInfo(),
            ];

            if ($exception instanceof ValidationException) {
                $body['errors'] = $exception->errors();
            }

            return response()->json($body, $status, ['Content-Type' => 'application/problem+json']);
        });
    })->create();
