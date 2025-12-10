<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeDetailResource;
use App\Http\Resources\RecipeTimelineResource;
use App\Http\Resources\RecipeRecommendationResource;
use App\Models\Recipe;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\Follow;
use App\Services\RecipeService;
use App\Traits\ApiResponse;
=======
use App\Services\RecipeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
>>>>>>> origin/main
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    use ApiResponse;

    public function __construct(
<<<<<<< HEAD
        protected RecipeService $service
    ) {
    }

    /**
     * GET /api/v1/recipes/timeline
     * butuh auth (sanctum)
     */
    public function timeline(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('Unauthenticated');
        }

        $perPage = min((int) $request->query('per_page', 10), 50);

        $query = Recipe::with('user')
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage);

        $recipeIds = collect($paginator->items())->pluck('id')->all();

        $likedIds = Like::where('user_id', $user->id)
            ->whereIn('recipe_id', $recipeIds)
            ->pluck('recipe_id')
            ->flip();

        $bookmarkedIds = Bookmark::where('user_id', $user->id)
            ->whereIn('recipe_id', $recipeIds)
            ->pluck('recipe_id')
            ->flip();

        $items = collect($paginator->items())->map(function ($recipe) use ($likedIds, $bookmarkedIds) {
            $recipe->is_liked = isset($likedIds[$recipe->id]);
            $recipe->is_bookmarked = isset($bookmarkedIds[$recipe->id]);

            return $recipe;
        });

        return response()->json([
            'success'    => true,
            'message'    => 'Timeline berhasil diambil',
            'data'       => RecipeTimelineResource::collection($items),
            'pagination' => [
                'current_page'   => $paginator->currentPage(),
                'last_page'      => $paginator->lastPage(),
                'per_page'       => $paginator->perPage(),
                'total'          => $paginator->total(),
                'from'           => $paginator->firstItem(),
                'to'             => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'timestamp'  => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID', (string) \Illuminate\Support\Str::uuid()),
            ],
        ]);
    }

    /**
     * GET /api/v1/recipes/recommendations
     */
    public function recommendations(Request $request)
    {
        $limit = min((int) $request->query('limit', 5), 10);

        $recipes = Recipe::query()
            ->select(['id', 'title', 'image', 'cooking_time', 'likes_count'])
            ->orderByDesc('likes_count')
            ->limit($limit)
            ->get();

        return $this->success(
            RecipeRecommendationResource::collection($recipes),
            'Rekomendasi resep berhasil diambil'
        );
    }

    /**
     * GET /api/v1/recipes/{id}
     * Bisa tanpa auth (tapi flag is_liked dll cuma true kalau pakai token)
     */
    public function show(Request $request, int $id)
    {
        $recipe = Recipe::with(['user', 'ingredients', 'steps'])
            ->find($id);
=======
        private readonly RecipeService $recipeService
    ) {}

    /**
     * Get timeline/feed
     * GET /recipes/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 50);
        $recipes = $this->recipeService->getTimeline($request->user(), $perPage);

        return $this->paginatedResponse($recipes, 'Timeline berhasil diambil');
    }

    /**
     * Get recommendations
     * GET /recipes/recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 5), 10);
        $recipes = $this->recipeService->getRecommendations($request->user(), $limit);

        return $this->successResponse($recipes, 'Rekomendasi resep berhasil diambil');
    }

    /**
     * Get recipe detail
     * GET /recipes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $recipe = $this->recipeService->getRecipeDetail($id, $request->user());
>>>>>>> origin/main

        if (!$recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

<<<<<<< HEAD
        $user = $request->user();

        $recipe->is_liked = false;
        $recipe->is_bookmarked = false;
        $recipe->is_followed = false;

        if ($user) {
            $recipe->is_liked = Like::where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->exists();

            $recipe->is_bookmarked = Bookmark::where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->exists();

            $recipe->is_followed = Follow::where('follower_id', $user->id)
                ->where('following_id', $recipe->user_id)
                ->exists();
        }

        return $this->success(
            new RecipeDetailResource($recipe),
            'Detail resep berhasil diambil'
        );
    }

    /**
     * POST /api/v1/recipes
     * multipart/form-data (ada file image)
     */
    public function store(StoreRecipeRequest $request)
    {
        try {
            $recipe = $this->service->createRecipe(
                $request->validated(),
                $request->user()->id
            );

            // set default flags untuk response
            $recipe->is_liked = false;
            $recipe->is_bookmarked = false;
            $recipe->is_followed = false;

            return $this->created(
                new RecipeDetailResource($recipe),
                'Resep berhasil dibagikan'
            );
        } catch (\Throwable $e) {
            return $this->error('Gagal membuat resep', [
                'exception' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * PUT /api/v1/recipes/{id}
     */
    public function update(UpdateRecipeRequest $request, int $id)
    {
        $recipe = Recipe::with(['user', 'ingredients', 'steps'])->find($id);

        if (!$recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if ($recipe->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Anda tidak memiliki akses untuk melakukan aksi ini');
        }

        try {
            $updated = $this->service->updateRecipe($recipe, $request->validated());

            $updated->is_liked = Like::where('user_id', $request->user()->id)
                ->where('recipe_id', $updated->id)
                ->exists();

            $updated->is_bookmarked = Bookmark::where('user_id', $request->user()->id)
                ->where('recipe_id', $updated->id)
                ->exists();

            $updated->is_followed = false;

            return $this->success(
                new RecipeDetailResource($updated),
                'Resep berhasil diperbarui'
            );
        } catch (\Throwable $e) {
            return $this->error('Gagal update resep', [
                'exception' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/recipes/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if ($recipe->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Anda tidak memiliki akses untuk melakukan aksi ini');
        }

        try {
            $this->service->deleteRecipe($recipe);

            return $this->success(null, 'Resep berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus resep', [
                'exception' => [$e->getMessage()],
            ], 500);
        }
    }
}
=======
        return $this->successResponse($recipe, 'Detail resep berhasil diambil');
    }

    /**
     * Like a recipe
     * POST /recipes/{id}/like
     */
    public function like(Request $request, int $id): JsonResponse
    {
        $result = $this->recipeService->likeRecipe($request->user(), $id);

        if (! $result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil menyukai resep');
    }

    /**
     * Unlike a recipe
     * DELETE /recipes/{id}/like
     */
    public function unlike(Request $request, int $id): JsonResponse
    {
        $result = $this->recipeService->unlikeRecipe($request->user(), $id);

        if (!$result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil batal menyukai resep');
    }
}
>>>>>>> origin/main
