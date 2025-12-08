<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Requests\Api\V1\User\UpdateLanguageRequest;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Get current user profile
     * GET /user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $profile = $this->userService->getCurrentUserProfile($request->user());

        return $this->successResponse($profile, 'Profil berhasil diambil');
    }

    /**
     * Update current user profile
     * PUT /user/profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->userService->updateProfile(
            $request->user(),
            $request->validated(),
            $request->file('avatar')
        );

        return $this->successResponse($profile, 'Profil berhasil diperbarui');
    }

    /**
     * Update language preference
     * PUT /user/language
     */
    public function updateLanguage(UpdateLanguageRequest $request): JsonResponse
    {
        $result = $this->userService->updateLanguage(
            $request->user(),
            $request->validated()['language']
        );

        return $this->successResponse($result, 'Bahasa berhasil diperbarui');
    }

    /**
     * Get user by username (public profile)
     * GET /users/{username}
     */
    public function show(Request $request, string $username): JsonResponse
    {
        $user = $this->userService->getUserByUsername($username, $request->user());

        if (!$user) {
            return $this->notFoundResponse('User tidak ditemukan');
        }

        return $this->successResponse($user, 'Data user berhasil diambil');
    }

    /**
     * Follow a user
     * POST /users/{username}/follow
     */
    public function follow(Request $request, string $username): JsonResponse
    {
        $result = $this->userService->followUser($request->user(), $username);

        if (! $result) {
            return $this->notFoundResponse('User tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil mengikuti user');
    }

    /**
     * Unfollow a user
     * DELETE /users/{username}/follow
     */
    public function unfollow(Request $request, string $username): JsonResponse
    {
        $result = $this->userService->unfollowUser($request->user(), $username);

        if (!$result) {
            return $this->notFoundResponse('User tidak ditemukan');
        }

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], 400);
        }

        return $this->successResponse($result, 'Berhasil berhenti mengikuti user');
    }

    /**
     * Get user's recipes
     * GET /users/{username}/recipes
     */
    public function recipes(Request $request, string $username): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $recipes = $this->userService->getUserRecipes($username, $request->user(), $perPage);

        if ($recipes === null) {
            return $this->notFoundResponse('User tidak ditemukan');
        }

        return $this->paginatedResponse($recipes, 'Resep user berhasil diambil');
    }
}