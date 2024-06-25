<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\JWT\JWTAuthController;
use App\Http\Resources\GeneralResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Sanctum routes
    Route::middleware(
        'auth:sanctum', 
        'ability:' . TokenAbility::ACCESS_API->value
        )->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/users', function (Request $request) {
            return new GeneralResource(
                200,
                'Users list',
                User::all()
            );
        });
    });

    // Sanctum auth routes
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        // Route::post('/register', 'register');
        // Route::post('/verify-email', 'verifyEmail');
        // Route::post('/forgot-password', 'forgotPassword');
        // Route::post('/reset-password', 'resetPassword');
        
        Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value)->group(function () {
            Route::get('/refresh', 'refreshToken');
        });
    });

    // JWT auth routes
    Route::controller(JWTAuthController::class)->prefix('auth/jwt')->group(function () {
        Route::post('/login', 'login');
        // Route::post('/register', 'register');
        // Route::post('/verify-email', 'verifyEmail');
        // Route::post('/forgot-password', 'forgotPassword');
        // Route::post('/reset-password', 'resetPassword');

        Route::middleware('auth:api')->group(function () {
            Route::get('/refresh', 'refreshToken');
            Route::get('/me', 'me');
            Route::post('/logout', 'logout');
            Route::get('/users', function (Request $request) {
                return new GeneralResource(
                    200,
                    'Users list',
                    User::all()
                );
            });
        });
    });
});