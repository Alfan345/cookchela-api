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
    public function getUserByUsername(string $username, ? User $currentUser = null): ? array
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
            'following_count' => $user->following_count ??  0,
            'recipes_count' => $user->recipes()->count(),
            'language' => $user->language?->value ?? 'id',
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
    \Log::info("[UserService] updateProfile input", $data);

    // Handle name update
    if (isset($data['name'])) {
        $updateData['name'] = $data['name'];
    }

    // Handle username update
    if (isset($data['username'])) {
        $updateData['username'] = $data['username'];
    }

    // Handle avatar upload
    if ($avatar) {
        try {
            \Log::info("[UserService] uploadAvatar run for user {$user->id}");
            $avatarPath = $this->uploadAvatar($user, $avatar);
            $updateData['avatar'] = $avatarPath;
            \Log::info("[UserService] uploadAvatar success", ['avatar' => $avatarPath]);
        } catch (\Exception $e) {
            \Log::error("[UserService] Gagal upload avatar: " . $e->getMessage());
            // Jika ingin error ke user, bisa lempar exception ke controller (atau return error di response JSON)
            throw new \Exception('Gagal upload avatar: ' . $e->getMessage(), 400);
        }
    }

    // Logging data yang akan diupdate ke database
    \Log::info("[UserService] user update data", $updateData);

    if (! empty($updateData)) {
        try {
            $user->update($updateData);
            $user->refresh();
            \Log::info("[UserService] user updated!", ['id' => $user->id, 'updateData' => $updateData]);
        } catch (\Exception $e) {
            \Log::error("[UserService] user update DB gagal: " . $e->getMessage());
            throw new \Exception('Gagal update data user: ' . $e->getMessage(), 400);
        }
    } else {
        \Log::warning("[UserService] Tidak ada data yang diupdate untuk user {$user->id}");
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

    \Log::info("[UserService] uploadAvatar: bucket {$bucket}, path {$path}");

    return \App\Services\SupabaseUploadService::upload($bucket, $path, $file);
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
    public function followUser(User $follower, string $username): ? array
    {
        $userToFollow = User::where('username', $username)->first();

        if (!$userToFollow) {
            return null;
        }

        // Cannot follow yourself
        if ($follower->id === $userToFollow->id) {
            return ['error' => 'Tidak dapat mengikuti diri sendiri'];
        }

        // Check if already following
        if ($follower->isFollowing($userToFollow)) {
            return ['error' => 'Sudah mengikuti user ini'];
        }

        DB::transaction(function () use ($follower, $userToFollow) {
            // Create follow relationship
            Follow::create([
                'follower_id' => $follower->id,
                'following_id' => $userToFollow->id,
            ]);

            // Increment counters
            $follower->increment('following_count');
            $userToFollow->increment('followers_count');
        });

        $userToFollow->refresh();

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

        if (! $userToUnfollow) {
            return null;
        }

        // Check if not following
        if (! $follower->isFollowing($userToUnfollow)) {
            return ['error' => 'Tidak mengikuti user ini'];
        }

        DB::transaction(function () use ($follower, $userToUnfollow) {
            // Remove follow relationship
            Follow::where('follower_id', $follower->id)
                ->where('following_id', $userToUnfollow->id)
                ->delete();

            // Decrement counters
            $follower->decrement('following_count');
            $userToUnfollow->decrement('followers_count');
        });

        $userToUnfollow->refresh();

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
            ->with('user:id,name,username,avatar')
            ->latest()
            ->paginate($perPage);
    }
}