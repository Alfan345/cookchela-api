<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BookmarkService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BookmarkService $bookmarkService
    ) {}

    /**
     * Get user's bookmarks
     * GET /bookmarks
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 10), 50);
        $bookmarks = $this->bookmarkService->getBookmarks($request->user(), $perPage);

        return $this->paginatedResponse($bookmarks, 'Bookmark berhasil diambil');
    }

    /**
     * Add recipe to bookmarks
     * POST /bookmarks/{recipeId}
     */
    public function store(Request $request, int $recipeId): JsonResponse
    {
        $result = $this->bookmarkService->addBookmark($request->user(), $recipeId);

        if (! $result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->createdResponse($result, 'Resep berhasil di-bookmark');
    }

    /**
     * Remove recipe from bookmarks
     * DELETE /bookmarks/{recipeId}
     */
    public function destroy(Request $request, int $recipeId): JsonResponse
    {
        $result = $this->bookmarkService->removeBookmark($request->user(), $recipeId);

        if (!$result) {
            return $this->notFoundResponse('Resep tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Bookmark berhasil dihapus');
    }

    /**
     * Check if recipe is bookmarked
     * GET /bookmarks/{recipeId}/check
     */
    public function check(Request $request, int $recipeId): JsonResponse
    {
        $isBookmarked = $this->bookmarkService->isBookmarked($request->user(), $recipeId);

        return $this->successResponse([
            'is_bookmarked' => $isBookmarked,
        ], 'Status bookmark berhasil diambil');
    }
}