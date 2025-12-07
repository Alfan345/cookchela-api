<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\SearchController;
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
    // User Routes (Protected) - Current User
    // ==========================================
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {

        // Get current user profile
        Route::get('/profile', [UserController::class, 'profile'])
            ->name('user.profile');

        // Update current user profile
        Route::put('/profile', [UserController::class, 'updateProfile'])
            ->name('user.profile.update');

        // Update language preference
        Route::put('/language', [UserController::class, 'updateLanguage'])
            ->name('user.language.update');

    });

    // ==========================================
    // Users Routes (Public Profile)
    // ==========================================
    Route::prefix('users')->group(function () {

        // Get user by username (public profile) - dapat diakses tanpa login
        Route::get('/{username}', [UserController::class, 'show'])
            ->name('users.show');

        // Get user's recipes - dapat diakses tanpa login
        Route::get('/{username}/recipes', [UserController::class, 'recipes'])
            ->name('users.recipes');

        // Follow/Unfollow - memerlukan login
        Route::middleware('auth:sanctum')->group(function () {

            // Follow user
            Route::post('/{username}/follow', [UserController::class, 'follow'])
                ->name('users.follow');

            // Unfollow user
            Route::delete('/{username}/follow', [UserController::class, 'unfollow'])
                ->name('users. unfollow');

        });

    });

    // ==========================================
    // Recipe Routes
    // ==========================================
    Route::prefix('recipes')->group(function () {

        // Timeline/Feed - dapat diakses tanpa login (tapi is_liked, is_bookmarked akan false)
        Route::get('/timeline', [RecipeController::class, 'timeline'])
            ->name('recipes. timeline');

        // Recommendations - dapat diakses tanpa login
        Route::get('/recommendations', [RecipeController::class, 'recommendations'])
            ->name('recipes.recommendations');

        // Get recipe detail - dapat diakses tanpa login
        Route::get('/{id}', [RecipeController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('recipes.show');

        // Like/Unlike - memerlukan login
        Route::middleware('auth:sanctum')->group(function () {

            // Like recipe
            Route::post('/{id}/like', [RecipeController::class, 'like'])
                ->where('id', '[0-9]+')
                ->name('recipes.like');

            // Unlike recipe
            Route::delete('/{id}/like', [RecipeController::class, 'unlike'])
                ->where('id', '[0-9]+')
                ->name('recipes. unlike');

        });

    });

    // ==========================================
    // Bookmark Routes (Protected)
    // ==========================================
    Route::prefix('bookmarks')->middleware('auth:sanctum')->group(function () {

        // Get user's bookmarks
        Route::get('/', [BookmarkController::class, 'index'])
            ->name('bookmarks. index');

        // Add bookmark
        Route::post('/{recipeId}', [BookmarkController::class, 'store'])
            ->where('recipeId', '[0-9]+')
            ->name('bookmarks.store');

        // Remove bookmark
        Route::delete('/{recipeId}', [BookmarkController::class, 'destroy'])
            ->where('recipeId', '[0-9]+')
            ->name('bookmarks.destroy');

        // Check if bookmarked
        Route::get('/{recipeId}/check', [BookmarkController::class, 'check'])
            ->where('recipeId', '[0-9]+')
            ->name('bookmarks.check');

    });

    // ==========================================
    // Search Routes
    // ==========================================
    Route::prefix('search')->group(function () {

        // Search recipes - dapat diakses tanpa login
        Route::get('/recipes', [SearchController::class, 'recipes'])
            ->name('search.recipes');

        // Search by ingredients - dapat diakses tanpa login
        Route::post('/ingredients', [SearchController::class, 'byIngredients'])
            ->name('search.ingredients');

        // Get suggestions/autocomplete - dapat diakses tanpa login
        Route::get('/suggestions', [SearchController::class, 'suggestions'])
            ->name('search.suggestions');

        // Search history - memerlukan login
        Route::middleware('auth:sanctum')->group(function () {

            // Get search history
            Route::get('/history', [SearchController::class, 'history'])
                ->name('search.history');

            // Clear all search history
            Route::delete('/history', [SearchController::class, 'clearHistory'])
                ->name('search.history.clear');

            // Delete specific search history
            Route::delete('/history/{keyword}', [SearchController::class, 'deleteHistory'])
                ->name('search.history. delete');

        });

    });

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