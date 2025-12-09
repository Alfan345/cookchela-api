<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterIngredient extends Model
{
    public $timestamps = false; // sesuai ERD

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'created_at',
    ];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_ingredient_tags',
            'master_ingredient_id',
            'recipe_id'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id');
    }
}
