<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string', 'in:id,en'],
        ];
    }

    public function messages(): array
    {
        return [
            'language. required' => 'Bahasa harus diisi',
            'language.in' => 'Bahasa harus id atau en',
        ];
    }
}