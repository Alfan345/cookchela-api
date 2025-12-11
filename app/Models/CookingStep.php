<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookingStep extends Model
{
    protected $table = 'cooking_steps';

    protected $fillable = [
        'recipe_id',
        'step_number',
        'description',
        'image',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Cooking step belongs to a recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // kalau sudah full URL, langsung balikin
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $supabaseUrl = config('services.supabase.url');
        $bucket      = config('services.supabase.bucket_recipes', 'recipes');

        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$this->image}";
    }
}
