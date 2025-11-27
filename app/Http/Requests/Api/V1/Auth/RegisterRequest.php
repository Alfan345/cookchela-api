<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request. 
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:users,username',
                'alpha_dash', // Only letters, numbers, dashes, underscores
                'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/', // Must start with letter
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // Requires password_confirmation field
            ],
        ];
    }

    /**
     * Get custom messages for validator errors. 
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Nama wajib diisi',
            'name.min' => 'Nama minimal 2 karakter',
            'name.max' => 'Nama maksimal 100 karakter',

            // Username
            'username. required' => 'Username wajib diisi',
            'username.min' => 'Username minimal 3 karakter',
            'username.max' => 'Username maksimal 50 karakter',
            'username.unique' => 'Username sudah digunakan',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash (-), dan underscore (_)',
            'username.regex' => 'Username harus diawali dengan huruf',

            // Email
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',

            // Password
            'password. required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ],
            ], 422)
        );
    }
}