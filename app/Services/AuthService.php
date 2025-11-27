<?php

namespace App\Services;

use App\Models\User;
use App\Enums\Language;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Exception;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'language' => Language::ID,
            'followers_count' => 0,
            'following_count' => 0,
        ]);

        $deviceName = $data['device_name'] ?? 'default';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->formatAuthResponse($user, $token, false);
    }

    /**
     * Login with email and password
     */
    public function login(array $data): ? array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        if ($user->password === null) {
            return null;
        }

        $deviceName = $data['device_name'] ??  'default';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->formatAuthResponse($user, $token, false);
    }

    /**
     * Login with Google OAuth
     */
    public function loginWithGoogle(array $data): ?array
    {
        try {
            $googleUser = $this->verifyGoogleToken($data['id_token']);

            if (! $googleUser) {
                return null;
            }

            $user = User::where('google_id', $googleUser['sub'])
                ->orWhere('email', $googleUser['email'])
                ->first();

            $isNewUser = false;

            if (! $user) {
                $isNewUser = true;
                $user = User::create([
                    'name' => $googleUser['name'],
                    'username' => User::generateUniqueUsername($googleUser['email']),
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['sub'],
                    'avatar' => $googleUser['picture'] ?? null,
                    'email_verified_at' => now(),
                    'language' => Language::ID,
                    'followers_count' => 0,
                    'following_count' => 0,
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['sub'],
                        'email_verified_at' => $user->email_verified_at ??  now(),
                    ]);
                }

                if (! $user->avatar && isset($googleUser['picture'])) {
                    $user->update(['avatar' => $googleUser['picture']]);
                }
            }

            $deviceName = $data['device_name'] ??  'google';
            $token = $user->createToken($deviceName)->plainTextToken;

            return $this->formatAuthResponse($user, $token, $isNewUser);

        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Verify Google ID Token
     */
    private function verifyGoogleToken(string $idToken): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->failed()) {
                return null;
            }

            $payload = $response->json();

            $clientId = config('services.google.client_id');
            $androidClientId = config('services.google.android_client_id');
            $iosClientId = config('services.google.ios_client_id');

            $validAudiences = array_filter([$clientId, $androidClientId, $iosClientId]);

            if (!empty($validAudiences) && ! in_array($payload['aud'] ?? '', $validAudiences)) {
                return null;
            }

            return [
                'sub' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'] ?? $payload['email'],
                'picture' => $payload['picture'] ?? null,
            ];

        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Refresh token
     */
    public function refreshToken(User $user): array
    {
        $tokenName = $user->currentAccessToken()->name ??  'default';
        $user->currentAccessToken()->delete();
        $newToken = $user->createToken($tokenName)->plainTextToken;

        return [
            'token' => [
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expires_at' => now()->addMinutes(config('sanctum.expiration', 1440))->toISOString(),
            ],
        ];
    }

    /**
     * Format authentication response
     */
    private function formatAuthResponse(User $user, string $token, bool $isNewUser = false): array
    {
        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'followers_count' => $user->followers_count ?? 0,
                'following_count' => $user->following_count ?? 0,
                'recipes_count' => $user->recipes()->count(), // ✅ Sekarang bisa! 
                'language' => $user->language?->value ?? 'id',
                'created_at' => $user->created_at->toISOString(),
            ],
            'token' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addMinutes(config('sanctum. expiration', 1440))->toISOString(),
            ],
        ];

        if ($isNewUser) {
            $response['is_new_user'] = true;
        }

        return $response;
    }
}