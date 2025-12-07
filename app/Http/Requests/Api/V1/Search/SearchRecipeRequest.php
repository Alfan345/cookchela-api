<?php

namespace App\Http\Requests\Api\V1\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1', 'max:255'],
            'sort_by' => ['sometimes', 'string', 'in:relevance,newest,popular,cooking_time'],
            'cooking_time_max' => ['sometimes', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Kata kunci pencarian harus diisi',
            'q.min' => 'Kata kunci pencarian minimal 1 karakter',
            'q.max' => 'Kata kunci pencarian maksimal 255 karakter',
        ];
    }
}