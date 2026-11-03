<?php

namespace App\Http\Requests\Master;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        $isCreate = $this->isMethod('POST');

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'min:6',
            ],
            'role' => [
                'required',
                new Enum(UserRole::class),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda strip, atau garis bawah.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi untuk pengguna baru.',
            'password.min'      => 'Password minimal terdiri dari 6 karakter.',
            'role.required'     => 'Peran akun wajib dipilih.',
        ];
    }
}

