<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $locationId = $this->route('location') ? $this->route('location')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'name')->ignore($locationId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lokasi/ruangan wajib diisi.',
            'name.unique'   => 'Nama lokasi/ruangan sudah digunakan.',
            'name.max'      => 'Nama lokasi/ruangan maksimal 100 karakter.',
        ];
    }
}

