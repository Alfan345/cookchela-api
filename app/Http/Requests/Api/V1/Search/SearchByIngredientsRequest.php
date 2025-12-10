<?php

namespace App\Http\Requests\Api\V1\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchByIngredientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ingredients' => ['required', 'array', 'min:1', 'max:10'],
            'ingredients.*' => ['required', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'ingredients.required' => 'Bahan-bahan harus diisi',
            'ingredients.array' => 'Bahan-bahan harus berupa array',
            'ingredients.min' => 'Minimal 1 bahan',
            'ingredients.max' => 'Maksimal 10 bahan',
        ];
    }
}