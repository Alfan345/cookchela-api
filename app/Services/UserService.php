<?php

namespace App\Services;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\SupabaseUploadService;
use Exception;

class UserService
{
    /**
     * Get user profile by username
     */
    public function getUserByUsername(string $username, ? User $currentUser = null): ?array
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return null;
        }

        $isFollowed = false;
        if ($currentUser && $currentUser->id !== $user->id) {
            $isFollowed = $currentUser->isFollowing($user);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'followers_count' => $user->followers_count ??  0,
            'following_count' => $user->following_count ?? 0,
            'recipes_count' => $user->recipes()->count(),
            'is_followed' => $isFollowed,
            'created_at' => $user->created_at->toISOString(),
        ];
    }

    /**
     * Get current user profile
     */
    public function getCurrentUserProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'followers_count' => $user->followers_count ?? 0,
            'following_count' => $user->following_count ?? 0,
            'recipes_count' => $user->recipes()->count(),
            'language' => ($user->language ? $user->language->value : 'id'),
            'email_verified_at' => ($user->email_verified_at?->toISOString()) ?? null,
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data, ? UploadedFile $avatar = null): array
    {
        $updateData = [];

        // Logging input data
        \Log::info("[UserService] updateProfile called", [
            'user_id' => $user->id,
            'input_data' => $data,
            'has_avatar' => $avatar !== null,
        ]);

        \Log::info("[UserService] Current user data BEFORE update", [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ]);

        // Handle name update
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
            \Log::info("[UserService] Name will be updated to: {$data['name']}");
        }

        // Handle username update
        if (isset($data['username'])) {
            $updateData['username'] = $data['username'];
            \Log::info("[UserService] Username will be updated to:  {$data['username']}");
        }

        // Handle avatar upload
        if ($avatar) {
            try {
                \Log::info("[UserService] Starting avatar upload for user {$user->id}");
                $avatarPath = $this->uploadAvatar($user, $avatar);
                $updateData['avatar'] = $avatarPath;
                \Log:: info("[UserService] Avatar upload success", ['avatar_path' => $avatarPath]);
            } catch (\Exception $e) {
                \Log:: error("[UserService] Avatar upload failed: " . $e->getMessage());
                throw new \Exception('Gagal upload avatar:  ' . $e->getMessage(), 400);
            }
        }

        // Logging data yang akan diupdate ke database
        \Log::info("[UserService] Data to be updated", ['updateData' => $updateData]);

        if (! empty($updateData)) {
            try {
                \Log::info("[UserService] Executing DB update.. .");
                
                // Update menggunakan Eloquent
                $updated = $user->update($updateData);
                
                \Log::info("[UserService] DB update() returned", ['result' => $updated]);
                
                // Refresh model dari database
                $user->refresh();
                
                \Log::info("[UserService] User data AFTER update and refresh", [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'updated_at' => $user->updated_at,
                ]);
                
            } catch (\Exception $e) {
                \Log::error("[UserService] DB update failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw new \Exception('Gagal update data user: ' . $e->getMessage(), 500);
            }
        } else {
            \Log::warning("[UserService] No data to update for user {$user->id}");
        }

        return $this->getCurrentUserProfile($user);
    }

    /**
     * Upload avatar to Supabase Storage
     */
    private function uploadAvatar(User $user, UploadedFile $file): string
    {
        $filename = 'avatar_' . time() . '.' . $file->getClientOriginalExtension();
        $path = "{$user->id}/{$filename}";
        $bucket = config('services.supabase.bucket_avatars', 'avatars');

        \Log::info("[UserService] uploadAvatar:  bucket={$bucket}, path={$path}");

        return SupabaseUploadService::upload($bucket, $path, $file);
    }

    /**
     * Update user language preference
     */
    public function updateLanguage(User $user, string $language): array
    {
        $user->update(['language' => $language]);

        return [
            'language' => $language,
        ];
    }

    /**
     * Follow a user
     */
    public function followUser(User $follower, string $username): ?array
    {
        $userToFollow = User::where('username', $username)->first();

        if (!$userToFollow) {
            return null;
        }

        // Cannot follow yourself
        if ($follower->id === $userToFollow->id) {
            throw new \Exception('Tidak dapat mengikuti diri sendiri', 400);
        }

        // Check if already following
        $exists = Follow::where('follower_id', $follower->id)
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($exists) {
            throw new \Exception('Anda sudah mengikuti user ini', 400);
        }

        // Create follow relationship
        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $userToFollow->id,
        ]);

        // Update counters
        $userToFollow->increment('followers_count');
        $follower->increment('following_count');

        return [
            'is_followed' => true,
            'followers_count' => $userToFollow->followers_count,
        ];
    }

    /**
     * Unfollow a user
     */
    public function unfollowUser(User $follower, string $username): ?array
    {
        $userToUnfollow = User::where('username', $username)->first();

        if (!$userToUnfollow) {
            return null;
        }

        // Delete follow relationship
        $deleted = Follow::where('follower_id', $follower->id)
            ->where('following_id', $userToUnfollow->id)
            ->delete();

        if (! $deleted) {
            throw new \Exception('Anda tidak mengikuti user ini', 400);
        }

        // Update counters
        $userToUnfollow->decrement('followers_count');
        $follower->decrement('following_count');

        return [
            'is_followed' => false,
            'followers_count' => $userToUnfollow->followers_count,
        ];
    }

    /**
     * Get user's recipes
     */
    public function getUserRecipes(string $username, ?User $currentUser = null, int $perPage = 10)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return null;
        }

        return $user->recipes()
            ->with(['user', 'ingredients', 'steps'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!\Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Password saat ini tidak sesuai', 400);
        }

        // Update password
        $user->update([
            'password' => \Hash::make($newPassword),
        ]);

        \Log::info("[UserService] Password changed for user {$user->id}");

        return true;
    }

    /**
     * Change user email
     */
    public function changeEmail(User $user, string $newEmail, string $password): bool
    {
        // Verify password
        if (!\Hash::check($password, $user->password)) {
            throw new \Exception('Password tidak sesuai', 400);
        }

        // Update email and reset verification
        $user->update([
            'email' => $newEmail,
            'email_verified_at' => null, // Reset email verification
        ]);

        \Log::info("[UserService] Email changed for user {$user->id}");

        // TODO: Send verification email ke email baru
        // Mail::to($newEmail)->send(new VerifyEmail($user));

        return true;
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user, string $password): bool
    {
        // Verify password
        if (!\Hash::check($password, $user->password)) {
            throw new \Exception('Password tidak sesuai', 400);
        }

        \Log::info("[UserService] Deleting account for user {$user->id}");

        DB::transaction(function () use ($user) {
            // Delete user's recipes (cascade akan handle ingredients, steps, dll)
            $user->recipes()->delete();

            // Delete user's likes
            $user->likes()->delete();

            // Delete user's bookmarks
            $user->bookmarks()->delete();

            // Delete user's follows (as follower)
            Follow::where('follower_id', $user->id)->delete();

            // Delete user's follows (as following)
            Follow::where('following_id', $user->id)->delete();

            // Delete user's search history
            $user->searchHistories()->delete();

            // Delete user's tokens (logout dari semua device)
            $user->tokens()->delete();

            // Finally, delete user
            $user->delete();
        });

        \Log::info("[UserService] Account deleted successfully");

        return true;
    }

}