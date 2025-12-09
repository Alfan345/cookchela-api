<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\UserRecipeController;
use App\Http\Controllers\Api\V1\RecipeLikeController;

/*
| API Routes
|--------------------------------------------------------------------------
| Prefix /api sudah otomatis dari RouteServiceProvider,
| jadi di sini kita pakai prefix /v1 untuk versi API.
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // Authentication Routes (Public)
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/google', [AuthController::class, 'googleLogin'])->name('auth.google');
    });

    // ==========================================
    // Authentication Routes (Protected)
    // ==========================================
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::get('/check', [AuthController::class, 'check'])->name('auth.check');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    // ==========================================
    // Recipes Routes (Public)
    // ==========================================

    // Detail resep: auth optional untuk guest
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])
        ->whereNumber('recipe');

    // Daftar resep milik user tertentu (public)
    Route::get('/users/{username}/recipes', [UserRecipeController::class, 'index']);

    // ==========================================
    // Recipes Routes (Protected)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // Feed & Home sections (butuh auth)
        Route::get('/recipes/timeline', [RecipeController::class, 'timeline']);
        Route::get('/recipes/recommendations', [RecipeController::class, 'recommendations']);

        // CRUD
        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])
            ->whereNumber('recipe');
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])
            ->whereNumber('recipe');

        // Like / Unlike
        Route::post('/recipes/{recipe}/like', [RecipeLikeController::class, 'store'])
            ->whereNumber('recipe');
        Route::delete('/recipes/{recipe}/like', [RecipeLikeController::class, 'destroy'])
            ->whereNumber('recipe');
    });
});

// ==========================================
// Health Check
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'app'      => config('app.name'),
        'version'  => '1.0.0',
    ]);
})->name('health');
