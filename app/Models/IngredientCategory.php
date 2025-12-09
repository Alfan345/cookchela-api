<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'created_at',
    ];

    public function masterIngredients(): HasMany
    {
        return $this->hasMany(MasterIngredient::class, 'category_id');
    }
}
