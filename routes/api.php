<?php

use App\Http\Controllers\Api\V1\AuthController;
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

// ==========================================
// API Version 1
// ==========================================
Route::prefix('v1')->group(function () {

    // ==========================================
    // Authentication Routes (Public)
    // ==========================================
    Route::prefix('auth')->group(function () {

        // Register new user
        Route::post('/register', [AuthController::class, 'register'])
            ->name('auth.register');

        // Login with email & password
        Route::post('/login', [AuthController::class, 'login'])
            ->name('auth.login');

        // Login with Google OAuth
        Route::post('/google', [AuthController::class, 'googleLogin'])
            ->name('auth.google');

    });

    // ==========================================
    // Authentication Routes (Protected)
    // ==========================================
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {

        // Logout from current device
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('auth.logout');

        // Logout from all devices
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])
            ->name('auth.logout-all');

        // Refresh token
        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->name('auth.refresh');

        // Check auth status
        Route::get('/check', [AuthController::class, 'check'])
            ->name('auth.check');

        // Get current user
        Route::get('/me', [AuthController::class, 'me'])
            ->name('auth.me');

    });

    // ==========================================
    // TODO: User Routes
    // ==========================================
    // Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    //     // Profile routes
    // });

    // ==========================================
    // TODO: Recipe Routes
    // ==========================================
    // Route::prefix('recipes')->group(function () {
    //     // Recipe routes
    // });

    // ==========================================
    // TODO: Bookmark Routes
    // ==========================================
    // Route::prefix('bookmarks')->middleware('auth:sanctum')->group(function () {
    //     // Bookmark routes
    // });

    // ==========================================
    // TODO: Search Routes
    // ==========================================
    // Route::prefix('search')->group(function () {
    //     // Search routes
    // });

});

// ==========================================
// Health Check (untuk monitoring)
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'version' => '1.0.0',
    ]);
})->name('health');