<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserRecipeController;
use App\Http\Controllers\Api\V1\RecipeLikeController;


/*
| API Routes
|--------------------------------------------------------------------------|
| Prefix /api sudah otomatis dari RouteServiceProvider,
| di sini kita pakai prefix /v1 untuk versi API.
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
    // User Routes (Protected) - current user
    // ==========================================
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {

        // Get current user profile
        Route::get('/profile', [UserController::class, 'profile'])
            ->name('user.profile');

        // Update current user profile
        Route::match(['put', 'post'], '/profile', [UserController:: class, 'updateProfile'])
            ->name('user.profile. update');

        // Update language preference
        Route::put('/language', [UserController::class, 'updateLanguage'])
            ->name('user.language. update');

        // Change password
        Route::put('/password', [UserController::class, 'changePassword'])
            ->name('user.password.change');

        // Change email
        Route::put('/email', [UserController::class, 'changeEmail'])
            ->name('user.email.change');

        // Delete account
        Route::delete('/account', [UserController::class, 'deleteAccount'])
            ->name('user.account.delete');
    });

    // ==========================================
    // Users Routes (Public Profile)
    // ==========================================
    Route::prefix('users')->group(function () {

        // Public profile by username
        Route::get('/{username}', [UserController::class, 'show'])
            ->name('users.show');

        // Public: user's recipes
        Route::get('/{username}/recipes', [UserController::class, 'recipes'])
            ->name('users.recipes');

        // Follow / Unfollow (protected)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{username}/follow', [UserController::class, 'follow'])
                ->name('users.follow');

            Route::delete('/{username}/follow', [UserController::class, 'unfollow'])
                ->name('users.unfollow');
        });
    });

    // ==========================================
    // Recipe Routes
    // ==========================================
    Route::prefix('recipes')->group(function () {

        // Timeline / feed (guest boleh akses,
        // tapi is_liked & is_bookmarked hanya true kalau pakai token)
        Route::get('/timeline', [RecipeController::class, 'timeline'])
            ->name('recipes.timeline');

        // Recommendations (guest boleh)
        Route::get('/recommendations', [RecipeController::class, 'recommendations'])
            ->name('recipes.recommendations');

        // Detail resep (guest boleh)
        Route::get('/{id}', [RecipeController::class, 'show'])
            ->whereNumber('id')
            ->name('recipes.show');

        // Protected: CRUD + like/unlike
        Route::middleware('auth:sanctum')->group(function () {

            // Create recipe
            Route::post('/', [RecipeController::class, 'store'])
                ->name('recipes.store');

            // Update recipe
            Route::put('/{id}', [RecipeController::class, 'update'])
                ->whereNumber('id')
                ->name('recipes.update');

            // Delete recipe
            Route::delete('/{id}', [RecipeController::class, 'destroy'])
                ->whereNumber('id')
                ->name('recipes.destroy');

            // Like recipe
            Route::post('/{id}/like', [RecipeController::class, 'like'])
                ->whereNumber('id')
                ->name('recipes.like');

            // Unlike recipe
            Route::delete('/{id}/like', [RecipeController::class, 'unlike'])
                ->whereNumber('id')
                ->name('recipes.unlike');
        });
    });

    // ==========================================
    // Bookmark Routes (Protected)
    // ==========================================
    Route::prefix('bookmarks')->middleware('auth:sanctum')->group(function () {

        // Get user's bookmarks
        Route::get('/', [BookmarkController::class, 'index'])
            ->name('bookmarks.index');

        // Add bookmark
        Route::post('/{recipeId}', [BookmarkController::class, 'store'])
            ->whereNumber('recipeId')
            ->name('bookmarks.store');

        // Remove bookmark
        Route::delete('/{recipeId}', [BookmarkController::class, 'destroy'])
            ->whereNumber('recipeId')
            ->name('bookmarks.destroy');

        // Check if bookmarked
        Route::get('/{recipeId}/check', [BookmarkController::class, 'check'])
            ->whereNumber('recipeId')
            ->name('bookmarks.check');
    });

    // ==========================================
    // Search Routes
    // ==========================================
    Route::prefix('search')->group(function () {

        // Search recipes (guest boleh)
        Route::get('/recipes', [SearchController::class, 'recipes'])
            ->name('search.recipes');

        // Search by ingredients (guest boleh)
        Route::post('/ingredients', [SearchController::class, 'byIngredients'])
            ->name('search.ingredients');

        // Suggestions / autocomplete (guest boleh)
        Route::get('/suggestions', [SearchController::class, 'suggestions'])
            ->name('search.suggestions');

        // History (protected)
        Route::middleware('auth:sanctum')->group(function () {

            // Get history
            Route::get('/history', [SearchController::class, 'history'])
                ->name('search.history');

            // Clear all history
            Route::delete('/history', [SearchController::class, 'clearHistory'])
                ->name('search.history.clear');

            // Delete one keyword
            Route::delete('/history/{keyword}', [SearchController::class, 'deleteHistory'])
                ->name('search.history.delete');
        });
    });
});

// ==========================================
// Health Check
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
        'app'       => config('app.name'),
        'version'   => '1.0.0',
    ]);
})->name('health');

Route::get('/test-supabase', [App\Http\Controllers\TestSupabaseController::class, 'test']);
Route::post('/test-supabase-upload', [App\Http\Controllers\TestSupabaseController::class, 'testUpload']);