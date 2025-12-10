<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Nama minimal 2 karakter',
            'name.max' => 'Nama maksimal 100 karakter',
            'username.min' => 'Username minimal 3 karakter',
            'username. max' => 'Username maksimal 50 karakter',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash, dan underscore',
            'username.unique' => 'Username sudah digunakan',
            'avatar.image' => 'Avatar harus berupa gambar',
            'avatar.mimes' => 'Avatar harus berformat jpeg, png, jpg, atau webp',
            'avatar.max' => 'Ukuran avatar maksimal 2MB',
        ];
    }
}