<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    public $timestamps = false;

    protected $table = 'search_histories';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'keyword',
    ];

    protected $casts = [
        'searched_at' => 'datetime',
    ];

    const CREATED_AT = 'searched_at';
    const UPDATED_AT = null;

    /**
     * Search history belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Search history may belong to a recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}