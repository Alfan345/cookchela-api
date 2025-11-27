<?php

namespace App\Models;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        $supabaseUrl = config('services.supabase. url');
        $bucket = config('services. supabase.bucket_avatars', 'avatars');

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

    // TODO: Relationships berikut akan ditambahkan oleh tim terkait:
    // - likes() -> Tim Recipe
    // - bookmarks() -> Tim Bookmark
    // - followers() -> Tim User/Profile
    // - following() -> Tim User/Profile
    // - searchHistories() -> Tim Search

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
}