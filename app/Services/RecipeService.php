<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\CookingStep;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeService
{
    public function __construct(
        protected SupabaseStorageService $storage
    ) {}

    // ==========================================
    // TIMELINE & RECOMMENDATIONS
    // ==========================================

    /**
     * Get timeline/feed recipes
     */
    public function getTimeline(?User $user = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Recipe::with(['user:id,name,username,avatar'])
            ->latest();

        $recipes = $query->paginate($perPage);

        // Tambahkan flag is_liked & is_bookmarked
        $recipes->getCollection()->transform(function (Recipe $recipe) use ($user) {
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

        return $recipes->map(function (Recipe $recipe) {
            return [
                'id'           => $recipe->id,
                'title'        => $recipe->title,
                'image_url'    => $recipe->image_url,
                'cooking_time' => $recipe->cooking_time,
                'likes_count'  => $recipe->likes_count ?? 0,
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

        $isLiked       = false;
        $isBookmarked  = false;
        $isFollowed    = false;

        if ($user) {
            $isLiked      = $user->hasLiked($recipe);
            $isBookmarked = $user->hasBookmarked($recipe);
            if ($recipe->user_id !== $user->id) {
                $isFollowed = $user->isFollowing($recipe->user);
            }
        }

        return [
            'id'               => $recipe->id,
            'title'            => $recipe->title,
            'image_url'        => $recipe->image_url,
            'description'      => $recipe->description,
            'cooking_time'     => $recipe->cooking_time,
            'servings'         => $recipe->servings,
            'likes_count'      => $recipe->likes_count ?? 0,
            'bookmarks_count'  => $recipe->bookmarks_count ?? 0,
            'is_liked'         => $isLiked,
            'is_bookmarked'    => $isBookmarked,
            'user' => [
                'id'               => $recipe->user->id,
                'name'             => $recipe->user->name,
                'username'         => $recipe->user->username,
                'avatar_url'       => $recipe->user->avatar_url,
                'followers_count'  => $recipe->user->followers_count ?? 0,
                'is_followed'      => $isFollowed,
            ],
            'ingredients' => $recipe->ingredients->map(function ($ingredient) {
                return [
                    'id'       => $ingredient->id,
                    'name'     => $ingredient->name,
                    'quantity' => $ingredient->quantity,
                    'unit'     => $ingredient->unit,
                ];
            })->values(),
            'steps' => $recipe->steps->map(function ($step) {
                return [
                    'id'          => $step->id,
                    'step_number' => $step->step_number,
                    'description' => $step->description,
                    'image_url'   => $step->image_url,
                ];
            })->values(),
            'created_at' => optional($recipe->created_at)->toISOString(),
            'updated_at' => optional($recipe->updated_at)->toISOString(),
        ];
    }

    // ==========================================
    // LIKE / UNLIKE
    // ==========================================

    /**
     * Like a recipe
     */
    public function likeRecipe(User $user, int $recipeId): ?array
    {
        $recipe = Recipe::find($recipeId);
        if (!$recipe) {
            return null;
        }

        if ($user->hasLiked($recipe)) {
            return ['error' => 'Sudah menyukai resep ini'];
        }

        DB::transaction(function () use ($user, $recipe) {
            Like::create([
                'user_id'   => $user->id,
                'recipe_id' => $recipe->id,
            ]);

            $recipe->increment('likes_count');
        });

        $recipe->refresh();

        return [
            'is_liked'    => true,
            'likes_count' => $recipe->likes_count,
        ];
    }

    /**
     * Unlike a recipe
     */
    public function unlikeRecipe(User $user, int $recipeId): ?array
    {
        $recipe = Recipe::find($recipeId);
        if (!$recipe) {
            return null;
        }

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
            'is_liked'    => false,
            'likes_count' => $recipe->likes_count,
        ];
    }

    /**
     * Format recipe for list view
     */
    private function formatRecipeForList(Recipe $recipe, ?User $user = null): array
    {
        $isLiked      = false;
        $isBookmarked = false;

        if ($user) {
            $isLiked      = $user->hasLiked($recipe);
            $isBookmarked = $user->hasBookmarked($recipe);
        }

        return [
            'id'              => $recipe->id,
            'title'           => $recipe->title,
            'image_url'       => $recipe->image_url,
            'description'     => $recipe->description,
            'cooking_time'    => $recipe->cooking_time,
            'servings'        => $recipe->servings,
            'likes_count'     => $recipe->likes_count ?? 0,
            'bookmarks_count' => $recipe->bookmarks_count ?? 0,
            'is_liked'        => $isLiked,
            'is_bookmarked'   => $isBookmarked,
            'user' => [
                'id'         => $recipe->user->id,
                'name'       => $recipe->user->name,
                'username'   => $recipe->user->username,
                'avatar_url' => $recipe->user->avatar_url,
            ],
            'created_at' => optional($recipe->created_at)->toISOString(),
        ];
    }

    // ==========================================
    // CREATE / UPDATE / DELETE RECIPE
    // ==========================================

    /**
     * Buat resep baru + upload image (kalau ada)
     * - recipes.image NOT NULL -> kita isi '' dulu, lalu update setelah upload
     */
    public function createRecipe(array $data, int $userId): Recipe
    {
        return DB::transaction(function () use ($data, $userId) {
            $ingredients = $data['ingredients'] ?? [];
            $steps       = $data['cooking_steps'] ?? [];
            /** @var UploadedFile|null $imageFile */
            $imageFile   = $data['image'] ?? null;

            unset($data['ingredients'], $data['cooking_steps'], $data['image']);

            // Insert recipe dulu (image tidak boleh null)
            $recipe = Recipe::create([
                'user_id'         => $userId,
                'title'           => $data['title'],
                'description'     => $data['description'] ?? null,
                'cooking_time'    => $data['cooking_time'],
                'servings'        => $data['servings'],
                'likes_count'     => 0,
                'bookmarks_count' => 0,
                'image'           => '', // penting: biar lolos NOT NULL
            ]);

            // Upload image kalau ada
            if ($imageFile instanceof UploadedFile) {
                $imagePath      = $this->storage->uploadRecipeImage($recipe->id, $imageFile);
                $recipe->image  = $imagePath;
                $recipe->save();
            }

            // Insert ingredients + tags
            if (!empty($ingredients)) {
                [$ingredientRows, $tagRows] = $this->buildIngredientRowsAndTags($ingredients, $recipe->id);

                if ($ingredientRows) {
                    Ingredient::insert($ingredientRows);
                }
                if ($tagRows) {
                    DB::table('recipe_ingredient_tags')->insert($tagRows);
                }
            }

            // Insert cooking steps
            if (!empty($steps)) {
                $stepRows = $this->buildStepRows($steps, $recipe->id);

                if ($stepRows) {
                    CookingStep::insert($stepRows);
                }
            }

            return Recipe::with(['user', 'ingredients', 'steps'])
                ->findOrFail($recipe->id);
        });
    }

    /**
     * Update resep + optional update image
     * - kalau ingredients dikirim -> replace all
     * - kalau steps dikirim -> replace all
     */
    public function updateRecipe(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $ingredients = $data['ingredients'] ?? null;
            $steps       = $data['cooking_steps'] ?? null;
            /** @var UploadedFile|null $imageFile */
            $imageFile   = $data['image'] ?? null;

            unset($data['ingredients'], $data['cooking_steps'], $data['image']);

            // Update main recipe fields
            $recipe->fill([
                'title'        => $data['title']        ?? $recipe->title,
                'description'  => $data['description']  ?? $recipe->description,
                'cooking_time' => $data['cooking_time'] ?? $recipe->cooking_time,
                'servings'     => $data['servings']     ?? $recipe->servings,
            ]);
            $recipe->save();

            // Ganti image kalau ada yang baru
            if ($imageFile instanceof UploadedFile) {
                if ($recipe->image) {
                    $this->storage->deleteRecipeImage($recipe->image);
                }

                $imagePath     = $this->storage->uploadRecipeImage($recipe->id, $imageFile);
                $recipe->image = $imagePath;
                $recipe->save();
            }

            // Replace ingredients kalau dikirim
            if (is_array($ingredients)) {
                Ingredient::where('recipe_id', $recipe->id)->delete();
                DB::table('recipe_ingredient_tags')->where('recipe_id', $recipe->id)->delete();

                [$ingredientRows, $tagRows] = $this->buildIngredientRowsAndTags($ingredients, $recipe->id);

                if ($ingredientRows) {
                    Ingredient::insert($ingredientRows);
                }
                if ($tagRows) {
                    DB::table('recipe_ingredient_tags')->insert($tagRows);
                }
            }

            // Replace steps kalau dikirim
            if (is_array($steps)) {
                CookingStep::where('recipe_id', $recipe->id)->delete();

                $stepRows = $this->buildStepRows($steps, $recipe->id);

                if ($stepRows) {
                    CookingStep::insert($stepRows);
                }
            }

            return Recipe::with(['user', 'ingredients', 'steps'])
                ->findOrFail($recipe->id);
        });
    }

    /**
     * Hapus resep + semua relasi terkait
     */
    public function deleteRecipe(Recipe $recipe): void
    {
        DB::transaction(function () use ($recipe) {
            Ingredient::where('recipe_id', $recipe->id)->delete();
            CookingStep::where('recipe_id', $recipe->id)->delete();
            Like::where('recipe_id', $recipe->id)->delete();
            Bookmark::where('recipe_id', $recipe->id)->delete();

            DB::table('recipe_ingredient_tags')
                ->where('recipe_id', $recipe->id)
                ->delete();

            if ($recipe->image) {
                $this->storage->deleteRecipeImage($recipe->image);
            }

            $recipe->delete();
        });
    }

    // ==========================================
    // HELPER BUILDER
    // ==========================================

    /**
     * Build ingredient rows & tag rows.
     * - ingredients.name NOT NULL -> isi dari master_ingredients.name
     * - recipe_ingredient_tags hanya pakai created_at
     */
    private function buildIngredientRowsAndTags(array $ingredients, int $recipeId): array
    {
        $masterIds = collect($ingredients)
            ->pluck('master_ingredient_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $masterMap = $masterIds
            ? MasterIngredient::whereIn('id', $masterIds)->pluck('name', 'id')
            : collect();

        $now  = now();
        $rows = [];
        $tags = [];

        foreach ($ingredients as $item) {
            $masterId = $item['master_ingredient_id'] ?? null;

            // nama bahan diisi otomatis dari master
            $name = $item['name'] ?? null;
            if (!$name && $masterId) {
                $name = $masterMap[$masterId] ?? null;
            }

            $rows[] = [
                'recipe_id'            => $recipeId,
                'master_ingredient_id' => $masterId,
                'name'                 => $name,
                'quantity'             => $item['quantity'] ?? null,
                'unit'                 => $item['unit'] ?? null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];

            $tags[] = [
                'recipe_id'            => $recipeId,
                'master_ingredient_id' => $masterId,
                'created_at'           => $now,
            ];
        }

        return [$rows, $tags];
    }

    /**
     * Build step rows untuk CookingStep
     */
    private function buildStepRows(array $steps, int $recipeId): array
    {
        $now  = now();
        $rows = [];

        foreach ($steps as $step) {
            $rows[] = [
                'recipe_id'   => $recipeId,
                'step_number' => $step['step_number'],
                'description' => $step['description'],
                'image'       => $step['image'] ?? null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        return $rows;
    }
}
