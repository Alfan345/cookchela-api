<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientCategory extends Model
{
    protected $table = 'ingredient_categories';

    // Kita pakai created_at saja, tanpa updated_at
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'slug',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Category has many master ingredients
     */
    public function masterIngredients(): HasMany
    {
        return $this->hasMany(MasterIngredient::class, 'category_id');
    }
}
