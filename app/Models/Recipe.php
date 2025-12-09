<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'recipe_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(CookingStep::class, 'recipe_id')->orderBy('step_number');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'recipe_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'recipe_id');
    }
}
