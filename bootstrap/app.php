<?php

use App\Exceptions\GeneralException;
use App\Http\Resources\GeneralResource;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle not found http exceptions
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('*')) {
                throw new GeneralException(
                    'Route not found',
                    404
                );
            }
        })
        // Handle route not found exceptions
        ->render(function (RouteNotFoundException $e, Request $request) {
            throw new GeneralException(
                $e->getMessage(),
                401
            );
        })
        // Handle too many requests
        ->render(function (ThrottleRequestsException $e, Request $request) {
            throw new GeneralException(
                'Too many requests',
                429
            );
        })
        // handle validation errors
        ->render(function (ValidationException $e, Request $request) {
            return new GeneralResource(
                422,
                'Validation error',
                $e->errors()
            );
        })
        ;
    })->create();
