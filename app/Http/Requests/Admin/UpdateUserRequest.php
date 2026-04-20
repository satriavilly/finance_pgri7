<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', "unique:users,email,{$userId}"],
            'username' => ['required', 'string', 'alpha_dash', "unique:users,username,{$userId}"],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::min(8)],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ];
    }
}
