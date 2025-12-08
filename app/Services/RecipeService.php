<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeService
{
    /**
     * Get timeline/feed recipes
     */
    public function getTimeline(? User $user = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Recipe::with(['user:id,name,username,avatar'])
            ->latest();

        $recipes = $query->paginate($perPage);

        // Add is_liked and is_bookmarked flags
        $recipes->getCollection()->transform(function ($recipe) use ($user) {
            return $this->formatRecipeForList($recipe, $user);
        });

        return $recipes;
    }

    /**
     * Get recipe recommendations
     */
    public function getRecommendations(?User $user = null, int $limit = 5): array
    {
        $recipes = Recipe::with(['user:id,name,username,avatar'])
            ->orderByDesc('likes_count')
            ->limit($limit)
            ->get();

        return $recipes->map(function ($recipe) {
            return [
                'id' => $recipe->id,
                'title' => $recipe->title,
                'image_url' => $recipe->image_url,
                'cooking_time' => $recipe->cooking_time,
                'likes_count' => $recipe->likes_count ??  0,
            ];
        })->toArray();
    }

    /**
     * Get recipe detail
     */
    public function getRecipeDetail(int $id, ?User $user = null): ?array
    {
        $recipe = Recipe::with([
            'user:id,name,username,avatar,followers_count',
            'ingredients',
            'steps',
        ])->find($id);

        if (!$recipe) {
            return null;
        }

        $isLiked = false;
        $isBookmarked = false;
        $isFollowed = false;

        if ($user) {
            $isLiked = $user->hasLiked($recipe);
            $isBookmarked = $user->hasBookmarked($recipe);
            if ($recipe->user_id !== $user->id) {
                $isFollowed = $user->isFollowing($recipe->user);
            }
        }

        return [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'image_url' => $recipe->image_url,
            'description' => $recipe->description,
            'cooking_time' => $recipe->cooking_time,
            'servings' => $recipe->servings,
            'likes_count' => $recipe->likes_count ??  0,
            'bookmarks_count' => $recipe->bookmarks_count ??  0,
            'is_liked' => $isLiked,
            'is_bookmarked' => $isBookmarked,
            'user' => [
                'id' => $recipe->user->id,
                'name' => $recipe->user->name,
                'username' => $recipe->user->username,
                'avatar_url' => $recipe->user->avatar_url,
                'followers_count' => $recipe->user->followers_count ?? 0,
                'is_followed' => $isFollowed,
            ],
            'ingredients' => $recipe->ingredients->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity' => $ingredient->quantity,
                    'unit' => $ingredient->unit,
                ];
            }),
            'steps' => $recipe->steps->map(function ($step) {
                return [
                    'id' => $step->id,
                    'step_number' => $step->step_number,
                    'description' => $step->description,
                    'image_url' => $step->image_url,
                ];
            }),
            'created_at' => $recipe->created_at->toISOString(),
            'updated_at' => $recipe->updated_at->toISOString(),
        ];
    }

    /**
     * Like a recipe
     */
    public function likeRecipe(User $user, int $recipeId): ? array
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return null;
        }

        // Check if already liked
        if ($user->hasLiked($recipe)) {
            return ['error' => 'Sudah menyukai resep ini'];
        }

        DB::transaction(function () use ($user, $recipe) {
            Like::create([
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
            ]);

            $recipe->increment('likes_count');
        });

        $recipe->refresh();

        return [
            'is_liked' => true,
            'likes_count' => $recipe->likes_count,
        ];
    }

    /**
     * Unlike a recipe
     */
    public function unlikeRecipe(User $user, int $recipeId): ? array
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return null;
        }

        // Check if not liked
        if (!$user->hasLiked($recipe)) {
            return ['error' => 'Belum menyukai resep ini'];
        }

        DB::transaction(function () use ($user, $recipe) {
            Like::where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->delete();

            $recipe->decrement('likes_count');
        });

        $recipe->refresh();

        return [
            'is_liked' => false,
            'likes_count' => $recipe->likes_count,
        ];
    }

    /**
     * Format recipe for list view
     */
    private function formatRecipeForList(Recipe $recipe, ?User $user = null): array
    {
        $isLiked = false;
        $isBookmarked = false;

        if ($user) {
            $isLiked = $user->hasLiked($recipe);
            $isBookmarked = $user->hasBookmarked($recipe);
        }

        return [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'image_url' => $recipe->image_url,
            'description' => $recipe->description,
            'cooking_time' => $recipe->cooking_time,
            'servings' => $recipe->servings,
            'likes_count' => $recipe->likes_count ??  0,
            'bookmarks_count' => $recipe->bookmarks_count ??  0,
            'is_liked' => $isLiked,
            'is_bookmarked' => $isBookmarked,
            'user' => [
                'id' => $recipe->user->id,
                'name' => $recipe->user->name,
                'username' => $recipe->user->username,
                'avatar_url' => $recipe->user->avatar_url,
            ],
            'created_at' => $recipe->created_at->toISOString(),
        ];
    }
}