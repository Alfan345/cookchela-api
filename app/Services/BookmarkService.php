<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BookmarkService
{
    /**
     * Get user's bookmarked recipes
     */
    public function getBookmarks(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $bookmarks = Bookmark::with(['recipe.user:id,name,username,avatar'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->paginate($perPage);

        // Transform to recipe format
        $bookmarks->getCollection()->transform(function ($bookmark) use ($user) {
            $recipe = $bookmark->recipe;
            return [
                'id' => $recipe->id,
                'title' => $recipe->title,
                'image_url' => $recipe->image_url,
                'description' => $recipe->description,
                'cooking_time' => $recipe->cooking_time,
                'servings' => $recipe->servings,
                'likes_count' => $recipe->likes_count ??  0,
                'bookmarks_count' => $recipe->bookmarks_count ??  0,
                'is_liked' => $user->hasLiked($recipe),
                'is_bookmarked' => true,
                'user' => [
                    'id' => $recipe->user->id,
                    'name' => $recipe->user->name,
                    'username' => $recipe->user->username,
                    'avatar_url' => $recipe->user->avatar_url,
                ],
                'bookmarked_at' => $bookmark->created_at->toISOString(),
                'created_at' => $recipe->created_at->toISOString(),
            ];
        });

        return $bookmarks;
    }

    /**
     * Add recipe to bookmarks
     */
    public function addBookmark(User $user, int $recipeId): ?array
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return null;
        }

        // Check if already bookmarked
        if ($user->hasBookmarked($recipe)) {
            return ['error' => 'Resep sudah di-bookmark'];
        }

        DB::transaction(function () use ($user, $recipe) {
            Bookmark::create([
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
            ]);

            $recipe->increment('bookmarks_count');
        });

        $recipe->refresh();

        return [
            'is_bookmarked' => true,
            'bookmarks_count' => $recipe->bookmarks_count,
        ];
    }

    /**
     * Remove recipe from bookmarks
     */
    public function removeBookmark(User $user, int $recipeId): ?array
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return null;
        }

        // Check if not bookmarked
        if (!$user->hasBookmarked($recipe)) {
            return ['error' => 'Resep belum di-bookmark'];
        }

        DB::transaction(function () use ($user, $recipe) {
            Bookmark::where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->delete();

            $recipe->decrement('bookmarks_count');
        });

        $recipe->refresh();

        return [
            'is_bookmarked' => false,
            'bookmarks_count' => $recipe->bookmarks_count,
        ];
    }

    /**
     * Check if recipe is bookmarked
     */
    public function isBookmarked(User $user, int $recipeId): bool
    {
        return Bookmark::where('user_id', $user->id)
            ->where('recipe_id', $recipeId)
            ->exists();
    }
}