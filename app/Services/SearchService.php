<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\SearchHistory;
use App\Models\User;
use App\Models\MasterIngredient;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Search recipes
     */
    public function searchRecipes(
        string $query,
        ?User $user = null,
        int $perPage = 10,
        ? string $sortBy = 'relevance',
        ? int $cookingTimeMax = null
    ): LengthAwarePaginator {
        $recipeQuery = Recipe::with(['user:id,name,username,avatar'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                    ->orWhere('description', 'ILIKE', "%{$query}%");
            });

        // Filter by cooking time
        if ($cookingTimeMax) {
            $recipeQuery->where('cooking_time', '<=', $cookingTimeMax);
        }

        // Sort
        switch ($sortBy) {
            case 'newest':
                $recipeQuery->latest();
                break;
            case 'popular':
                $recipeQuery->orderByDesc('likes_count');
                break;
            case 'cooking_time':
                $recipeQuery->orderBy('cooking_time');
                break;
            default: // relevance
                $recipeQuery->orderByRaw("
                    CASE 
                        WHEN title ILIKE ? THEN 1
                        WHEN title ILIKE ?  THEN 2
                        ELSE 3
                    END
                ", ["{$query}%", "%{$query}%"]);
                break;
        }

        $recipes = $recipeQuery->paginate($perPage);

        // Save search history
        if ($user) {
            $this->saveSearchHistory($user, $query);
        }

        // Format results
        $recipes->getCollection()->transform(function ($recipe) use ($user) {
            return $this->formatRecipeResult($recipe, $user);
        });

        return $recipes;
    }

    /**
     * Search by ingredients
     */
    public function searchByIngredients(
        array $ingredients,
        ?User $user = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $recipeQuery = Recipe::with(['user:id,name,username,avatar', 'ingredients'])
            ->whereHas('ingredients', function ($q) use ($ingredients) {
                $q->where(function ($subQ) use ($ingredients) {
                    foreach ($ingredients as $ingredient) {
                        $subQ->orWhere('name', 'ILIKE', "%{$ingredient}%");
                    }
                });
            })
            ->withCount(['ingredients as matched_ingredients' => function ($q) use ($ingredients) {
                $q->where(function ($subQ) use ($ingredients) {
                    foreach ($ingredients as $ingredient) {
                        $subQ->orWhere('name', 'ILIKE', "%{$ingredient}%");
                    }
                });
            }])
            ->orderByDesc('matched_ingredients')
            ->orderByDesc('likes_count');

        $recipes = $recipeQuery->paginate($perPage);

        // Format results
        $recipes->getCollection()->transform(function ($recipe) use ($user, $ingredients) {
            $formatted = $this->formatRecipeResult($recipe, $user);
            $formatted['matched_ingredients'] = $recipe->matched_ingredients ??  0;
            return $formatted;
        });

        return $recipes;
    }

    /**
     * Get search suggestions/autocomplete
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        // Get recipe title suggestions
        $recipeSuggestions = Recipe::where('title', 'ILIKE', "%{$query}%")
            ->select('title')
            ->distinct()
            ->limit($limit)
            ->pluck('title')
            ->toArray();

        // Get ingredient suggestions
        $ingredientSuggestions = MasterIngredient::where('name', 'ILIKE', "%{$query}%")
            ->select('name')
            ->distinct()
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return [
            'recipes' => $recipeSuggestions,
            'ingredients' => $ingredientSuggestions,
        ];
    }

    /**
     * Get search history for user
     */
    public function getSearchHistory(User $user, int $limit = 10): array
    {
        return SearchHistory::where('user_id', $user->id)
            ->whereNotNull('keyword')
            ->orderBy('keyword')
            ->orderByDesc('searched_at')
            ->select('keyword', 'searched_at')
            ->distinct('keyword')
            ->limit($limit)
            ->get()
            ->map(function ($history) {
                return [
                    'keyword' => $history->keyword,
                    'searched_at' => $history->searched_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Clear search history
     */
    public function clearSearchHistory(User $user): void
    {
        SearchHistory::where('user_id', $user->id)->delete();
    }

    /**
     * Delete specific search history
     */
    public function deleteSearchHistory(User $user, string $keyword): void
    {
        SearchHistory::where('user_id', $user->id)
            ->where('keyword', $keyword)
            ->delete();
    }

    /**
     * Save search history
     */
    private function saveSearchHistory(User $user, string $keyword, ?int $recipeId = null): void
    {
        SearchHistory::create([
            'user_id' => $user->id,
            'recipe_id' => $recipeId,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Format recipe for search result
     */
    private function formatRecipeResult(Recipe $recipe, ?User $user = null): array
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