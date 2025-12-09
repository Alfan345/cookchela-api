<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'image'        => 'nullable|image|max:2048',
            'description'  => 'required|string',
            'cooking_time' => 'required|integer|min:1',
            'servings'     => 'required|integer|min:1',

            'ingredients'                             => 'required|array|min:1',
            'ingredients.*.master_ingredient_id'      => 'required|integer|exists:master_ingredients,id',
            'ingredients.*.quantity'                  => 'required|string|max:50',
            'ingredients.*.unit'                      => 'nullable|string|max:50',

            'cooking_steps'                           => 'required|array|min:1',
            'cooking_steps.*.step_number'             => 'required|integer|min:1',
            'cooking_steps.*.description'             => 'required|string',
        ];
    }
}
