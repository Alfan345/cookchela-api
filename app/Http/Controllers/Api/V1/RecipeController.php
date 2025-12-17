<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Services\RecipeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- TAMBAHAN PENTING

class RecipeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RecipeService $recipeService
    ) {}

    // ==========================================
    // TIMELINE & RECOMMENDATIONS
    // ==========================================

    /**
     * GET /api/v1/recipes/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        // ambil user dari token Sanctum kalau ada, kalau guest -> null
        $user = Auth::guard('sanctum')->user();

        $perPage = min((int) $request->input('per_page', 10), 50);
        $recipes = $this->recipeService->getTimeline($user, $perPage);

        return $this->paginatedResponse($recipes, 'Timeline berhasil diambil');
    }

    /**
     * GET /api/v1/recipes/recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        // boleh pakai user juga kalau nanti mau dipersonalisasi
        $user  = Auth::guard('sanctum')->user();
        $limit = min((int) $request->input('limit', 5), 10);

        $recipes = $this->recipeService->getRecommendations($user, $limit);

        return $this->successResponse($recipes, 'Rekomendasi resep berhasil diambil');
    }

    /**
     * GET /api/v1/recipes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // ambil user dari token Sanctum kalau ada
        $user   = Auth::guard('sanctum')->user();
        $recipe = $this->recipeService->getRecipeDetail($id, $user);

        if (! $recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        return $this->successResponse($recipe, 'Detail resep berhasil diambil');
    }

    // ==========================================
    // LIKE / UNLIKE
    // ==========================================

    /**
     * POST /api/v1/recipes/{id}/like
     */
    public function like(Request $request, int $id): JsonResponse
    {
        // di route sudah pakai auth:sanctum, jadi $request->user() pasti ada
        $result = $this->recipeService->likeRecipe($request->user(), $id);

        if (! $result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        // sekarang likeRecipe sudah idempotent, idealnya tidak ada 'error' lagi
        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil menyukai resep');
    }

    /**
     * DELETE /api/v1/recipes/{id}/like
     */
    public function unlike(Request $request, int $id): JsonResponse
    {
        $result = $this->recipeService->unlikeRecipe($request->user(), $id);

        if (! $result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil batal menyukai resep');
    }

    // ==========================================
    // CRUD RESEP
    // ==========================================

    /**
     * POST /api/v1/recipes
     * multipart/form-data
     */
    public function store(StoreRecipeRequest $request): JsonResponse
    {
        try {
            $recipe = $this->recipeService->createRecipe(
                $request->validated(),
                $request->user()->id
            );

            // ambil detail dengan format yang sama seperti show()
            $formatted = $this->recipeService->getRecipeDetail($recipe->id, $request->user());

            return $this->createdResponse(
                $formatted,
                'Resep berhasil dibagikan'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Gagal membuat resep',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    /**
     * PUT /api/v1/recipes/{id}
     */
    public function update(UpdateRecipeRequest $request, int $id): JsonResponse
    {
        $recipe = Recipe::with(['user', 'ingredients', 'steps'])->find($id);

        if (! $recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if ($recipe->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Anda tidak memiliki akses untuk melakukan aksi ini');
        }

        try {
            $updated   = $this->recipeService->updateRecipe($recipe, $request->validated());
            $formatted = $this->recipeService->getRecipeDetail($updated->id, $request->user());

            return $this->successResponse(
                $formatted,
                'Resep berhasil diperbarui'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Gagal update resep',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    /**
     * DELETE /api/v1/recipes/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $recipe = Recipe::find($id);

        if (! $recipe) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if ($recipe->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Anda tidak memiliki akses untuk melakukan aksi ini');
        }

        try {
            $this->recipeService->deleteRecipe($recipe);

            return $this->successResponse(null, 'Resep berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Gagal menghapus resep',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
