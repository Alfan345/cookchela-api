<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RecipeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    use ApiResponse;

    public function __construct(
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

        if (!$recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

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
