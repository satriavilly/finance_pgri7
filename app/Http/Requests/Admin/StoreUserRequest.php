<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'alpha_dash', 'unique:users,username'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', 'exists:roles,name'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'is_active' => ['boolean'],
        ];
    }
}
