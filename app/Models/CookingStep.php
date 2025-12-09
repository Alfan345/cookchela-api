<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookingStep extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'step_number',
        'description',
        'image',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Cooking step belongs to a recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $supabaseUrl = config('services.supabase. url');
        $bucket = config('services.supabase.bucket_recipes', 'recipes');

        return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$this->image}";
    }
}