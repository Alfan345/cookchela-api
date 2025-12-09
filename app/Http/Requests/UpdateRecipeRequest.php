<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'sometimes|string|max:255',
            'image'        => 'sometimes|nullable|image|max:2048',
            'description'  => 'sometimes|string|nullable',
            'cooking_time' => 'sometimes|integer|min:1',
            'servings'     => 'sometimes|integer|min:1',

            'ingredients'                             => 'sometimes|array|min:1',
            'ingredients.*.master_ingredient_id'      => 'required_with:ingredients|integer|exists:master_ingredients,id',
            'ingredients.*.quantity'                  => 'required_with:ingredients|string|max:50',
            'ingredients.*.unit'                      => 'nullable|string|max:50',

            'cooking_steps'                           => 'sometimes|array|min:1',
            'cooking_steps.*.step_number'             => 'required_with:cooking_steps|integer|min:1',
            'cooking_steps.*.description'             => 'required_with:cooking_steps|string',
        ];
    }
}
