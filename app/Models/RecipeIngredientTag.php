<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredientTag extends Model
{
    protected $table = 'recipe_ingredient_tags';

    protected $fillable = [
        'recipe_id',
        'master_ingredient_id',
    ];
}
