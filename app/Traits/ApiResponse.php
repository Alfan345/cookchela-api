<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $this->getMeta(),
        ], $code);
    }

    /**
     * Created response (201)
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'meta'    => $this->getMeta(),
        ], $code);
    }

    /**
     * Validation error response (422)
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validasi gagal'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response (404)
     */
    protected function notFoundResponse(
        string $message = 'Data tidak ditemukan'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response (401)
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response (403)
     */
    protected function forbiddenResponse(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully',
        ?string $resourceClass = null
    ): JsonResponse {
        $items = $resourceClass
            ? $resourceClass::collection($paginator->items())->resolve()
            : $paginator->items();

        return response()->json([
            'success'    => true,
            'message'    => $message,
            'data'       => $items,
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
            'meta' => $this->getMeta(),
        ]);
    }

    /**
     * Get meta information for response
     */
    private function getMeta(): array
    {
        return [
            'timestamp'  => now()->toISOString(),
            'request_id' => request()->header('X-Request-ID', (string) \Illuminate\Support\Str::uuid()),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Alias pendek (biar kode lama yang pakai success()/error() tetap jalan)
    |--------------------------------------------------------------------------
    */

    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->successResponse($data, $message, $code);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->createdResponse($data, $message);
    }

    protected function error(string $message = 'Error', mixed $errors = null, int $code = 400): JsonResponse
    {
        return $this->errorResponse($message, $code, $errors);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Data retrieved successfully', ?string $resourceClass = null): JsonResponse
    {
        return $this->paginatedResponse($paginator, $message, $resourceClass);
    }
}
