<?php
// routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
        Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    });

    // Protected routes
    Route::middleware('auth:api')->group(function () {

        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('stats', [UserController::class, 'stats']);
            Route::get('{user}', [UserController::class, 'show']);
            Route::put('profile', [UserController::class, 'update']);
            Route::delete('account', [UserController::class, 'destroy']);
        });

        // Trip routes
        Route::prefix('trips')->group(function () {
            Route::get('/', [TripController::class, 'index']);
            Route::post('/', [TripController::class, 'store']);
            Route::get('my-trips', [TripController::class, 'myTrips']);
            Route::get('{trip}', [TripController::class, 'show']);
            Route::put('{trip}', [TripController::class, 'update']);
            Route::delete('{trip}', [TripController::class, 'destroy']);
            Route::patch('{trip}/cancel', [TripController::class, 'cancel']);
            Route::patch('{trip}/complete', [TripController::class, 'complete']);
        });
    });

    // Health check route
    Route::get('health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });
});

// API Documentation route 
Route::get('/', function () {
    return response()->json([
        'message' => 'Trip Management API',
        'version' => '1.0.0',
        'documentation' => 'Available endpoints:',
        'endpoints' => [
            'auth' => [
                'POST /api/v1/auth/register' => 'Register a new user',
                'POST /api/v1/auth/login' => 'Login user',
                'POST /api/v1/auth/logout' => 'Logout user (requires auth)',
                'POST /api/v1/auth/refresh' => 'Refresh token (requires auth)',
                'GET /api/v1/auth/me' => 'Get current user (requires auth)',
            ],
            'users' => [
                'GET /api/v1/users' => 'Get all users (requires auth)',
                'GET /api/v1/users/{id}' => 'Get user by ID (requires auth)',
                'PUT /api/v1/users/profile' => 'Update current user profile (requires auth)',
                'DELETE /api/v1/users/account' => 'Delete current user account (requires auth)',
                'GET /api/v1/users/stats' => 'Get user statistics (requires auth)',
            ],
            'trips' => [
                'GET /api/v1/trips' => 'Get all trips with pagination & search (requires auth)',
                'POST /api/v1/trips' => 'Create new trip (requires auth)',
                'GET /api/v1/trips/my-trips' => 'Get current user trips (requires auth)',
                'GET /api/v1/trips/{id}' => 'Get trip by ID (requires auth)',
                'PUT /api/v1/trips/{id}' => 'Update trip (requires auth & ownership)',
                'DELETE /api/v1/trips/{id}' => 'Delete trip (requires auth & ownership)',
                'PATCH /api/v1/trips/{id}/cancel' => 'Cancel trip (requires auth & ownership)',
                'PATCH /api/v1/trips/{id}/complete' => 'Complete trip (requires auth & ownership)',
            ]
        ]
    ]);
});
