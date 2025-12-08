<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Search\SearchRecipeRequest;
use App\Http\Requests\Api\V1\Search\SearchByIngredientsRequest;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SearchService $searchService
    ) {}

    /**
     * Search recipes
     * GET /search/recipes
     */
    public function recipes(SearchRecipeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = min($validated['per_page'] ?? 10, 50);

        $recipes = $this->searchService->searchRecipes(
            $validated['q'],
            $request->user(),
            $perPage,
            $validated['sort_by'] ??  'relevance',
            $validated['cooking_time_max'] ?? null
        );

        return $this->paginatedResponse($recipes, 'Hasil pencarian berhasil diambil');
    }

    /**
     * Search by ingredients
     * POST /search/ingredients
     */
    public function byIngredients(SearchByIngredientsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = min($validated['per_page'] ?? 10, 50);

        $recipes = $this->searchService->searchByIngredients(
            $validated['ingredients'],
            $request->user(),
            $perPage
        );

        return $this->paginatedResponse($recipes, 'Hasil pencarian berdasarkan bahan berhasil diambil');
    }

    /**
     * Get search suggestions
     * GET /search/suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = min($request->input('limit', 10), 20);

        if (strlen($query) < 1) {
            return $this->successResponse([
                'recipes' => [],
                'ingredients' => [],
            ], 'Saran pencarian berhasil diambil');
        }

        $suggestions = $this->searchService->getSuggestions($query, $limit);

        return $this->successResponse($suggestions, 'Saran pencarian berhasil diambil');
    }

    /**
     * Get search history
     * GET /search/history
     */
    public function history(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 10), 20);
        $history = $this->searchService->getSearchHistory($request->user(), $limit);

        return $this->successResponse($history, 'Riwayat pencarian berhasil diambil');
    }

    /**
     * Clear search history
     * DELETE /search/history
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $this->searchService->clearSearchHistory($request->user());

        return $this->successResponse(null, 'Riwayat pencarian berhasil dihapus');
    }

    /**
     * Delete specific search history
     * DELETE /search/history/{keyword}
     */
    public function deleteHistory(Request $request, string $keyword): JsonResponse
    {
        $this->searchService->deleteSearchHistory($request->user(), $keyword);

        return $this->successResponse(null, 'Riwayat pencarian berhasil dihapus');
    }
}