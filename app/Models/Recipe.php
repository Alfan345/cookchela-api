<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recipe Model
 * 
 * TODO: Model ini akan dilengkapi oleh tim Recipe
 * - Tambahkan relationships (ingredients, steps, likes, bookmarks)
 * - Tambahkan helper methods
 * - Tambahkan accessors/mutators
 */
class Recipe extends Model
{
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

    /**
     * Recipe belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // TODO: Tambahkan relationships lainnya
    // - ingredients()
    // - steps()
    // - likes()
    // - bookmarks()
}