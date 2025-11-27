<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\GoogleLoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->createdResponse($result, 'Registrasi berhasil');
    }

    /**
     * Login with email and password
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->unauthorizedResponse('Email atau password salah');
        }

        return $this->successResponse($result, 'Login berhasil');
    }

    /**
     * Login with Google OAuth
     *
     * @param GoogleLoginRequest $request
     * @return JsonResponse
     */
    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithGoogle($request->validated());

        if (!$result) {
            return $this->unauthorizedResponse('Google authentication gagal');
        }

        $message = isset($result['is_new_user']) && $result['is_new_user']
            ? 'Registrasi dengan Google berhasil'
            : 'Login dengan Google berhasil';

        return $this->successResponse($result, $message);
    }

    /**
     * Logout current user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logout berhasil');
    }

    /**
     * Logout from all devices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->successResponse(null, 'Logout dari semua perangkat berhasil');
    }

    /**
     * Refresh access token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->user());

        return $this->successResponse($result, 'Token berhasil diperbarui');
    }

    /**
     * Check authentication status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        return $this->successResponse([
            'authenticated' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'token_name' => $token->name,
            'token_expires_at' => $token->expires_at?->toISOString(),
        ], 'Token valid');
    }

    /**
     * Get current authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'followers_count' => $user->followers_count ?? 0,
            'following_count' => $user->following_count ??  0,
            'recipes_count' => $user->recipes()->count(),
            'language' => $user->language?->value ??  'id',
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ], 'Data user berhasil diambil');
    }
}