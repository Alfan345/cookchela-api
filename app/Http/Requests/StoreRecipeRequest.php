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

            // === INGREDIENTS: user ketik sendiri ===
            'ingredients'             => 'required|array|min:1',
            'ingredients.*.name'      => 'required|string|max:255',
            'ingredients.*.quantity'  => 'nullable|string|max:50',
            'ingredients.*.unit'      => 'nullable|string|max:50',

            // === COOKING STEPS (tanpa foto per langkah) ===
            'cooking_steps'                   => 'required|array|min:1',
            'cooking_steps.*.step_number'     => 'required|integer|min:1',
            'cooking_steps.*.description'     => 'required|string',
        ];
    }
}
