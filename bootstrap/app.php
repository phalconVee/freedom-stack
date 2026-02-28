<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscribed' => \App\Http\Middleware\SubscriptionGate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure API error responses (e.g. 500) include CORS so the browser doesn't report "blocked by CORS".
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            if (! $request->is('api/*')) {
                return null;
            }
            // Let Laravel handle validation and HTTP exceptions (422, 404, etc.) with correct status
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return null;
            }
            $allowed = config('cors.allowed_origins', []);
            $origin = $request->header('Origin');
            $corsOrigin = is_array($allowed) && $origin && in_array($origin, $allowed, true)
                ? $origin
                : (is_array($allowed) && count($allowed) > 0 ? $allowed[0] : '*');

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $body = ['message' => config('app.debug') ? $e->getMessage() : 'Server Error'];
            if (config('app.debug')) {
                $body['exception'] = get_class($e);
                $body['file'] = $e->getFile();
                $body['line'] = $e->getLine();
            }
            $response = response()->json($body, $status);
            $response->headers->set('Access-Control-Allow-Origin', $corsOrigin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');

            return $response;
        });
    })->create();
