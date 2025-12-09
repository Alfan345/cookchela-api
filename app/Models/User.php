<?php

namespace App\Models;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'google_id',
        'followers_count',
        'following_count',
        'language',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'language' => Language::class,
            'followers_count' => 'integer',
            'following_count' => 'integer',
        ];
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getAvatarUrlAttribute(): ? string
    {
        if (! $this->avatar) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        $supabaseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket_avatars', 'avatars');

        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$this->avatar}";
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User has many recipes
     * NOTE: Recipe model dibuat oleh tim Recipe
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * User has many likes
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Recipes that user has liked
     */
    public function likedRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'likes', 'user_id', 'recipe_id')
            ->withPivot('created_at');
    }

    /**
     * User has many bookmarks
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Recipes that user has bookmarked
     */
    public function bookmarkedRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'bookmarks', 'user_id', 'recipe_id')
            ->withPivot('created_at');
    }

    /**
     * Users that this user is following
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withPivot('created_at');
    }

    /**
     * Users that follow this user
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withPivot('created_at');
    }

    /**
     * User has many search histories
     */
    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public static function generateUniqueUsername(string $base): string
    {
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $base));
        $username = substr($username, 0, 40);

        $originalUsername = $username;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }
    /**
     * Check if user is following another user
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if user has liked a recipe
     */
    public function hasLiked(Recipe $recipe): bool
    {
        return $this->likes()->where('recipe_id', $recipe->id)->exists();
    }

    /**
     * Check if user has bookmarked a recipe
     */
    public function hasBookmarked(Recipe $recipe): bool
    {
        return $this->bookmarks()->where('recipe_id', $recipe->id)->exists();
    }
    
}