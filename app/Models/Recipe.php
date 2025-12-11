<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $table = 'recipes';

    protected $fillable = [
        'user_id',
        'title',
        'image',
        'description',
        'cooking_time',
        'servings',
        'likes_count',
        'bookmarks_count',
    ];

    protected $casts = [
        'cooking_time'     => 'integer',
        'servings'         => 'integer',
        'likes_count'      => 'integer',
        'bookmarks_count'  => 'integer',
    ];

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Kalau sudah full URL, langsung return
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $supabaseUrl = config('services.supabase.url');
        $bucket      = config('services.supabase.bucket_recipes', 'recipes');

        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$this->image}";
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Recipe belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recipe has many ingredients
     */
    public function ingredients(): HasMany
    {
        // pakai 'recipe_id' sesuai skema kamu
        return $this->hasMany(Ingredient::class, 'recipe_id');
    }

    /**
     * Recipe has many cooking steps
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CookingStep::class, 'recipe_id')
                    ->orderBy('step_number');
    }

    /**
     * Recipe has many likes
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'recipe_id');
    }

    /**
     * Users who liked this recipe
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes', 'recipe_id', 'user_id')
                    ->withPivot('created_at');
    }

    /**
     * Recipe has many bookmarks
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'recipe_id');
    }

    /**
     * Users who bookmarked this recipe
     */
    public function bookmarkedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'recipe_id', 'user_id')
                    ->withPivot('created_at');
    }
}
