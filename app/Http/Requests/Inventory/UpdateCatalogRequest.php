<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'category_id'   => ['required', 'exists:categories,id'],
            'brand'         => ['nullable', 'string', 'max:100'],
            'specification' => ['nullable', 'string'],
            'unit_name'     => ['required', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
            'photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'unit_name.required'   => 'Satuan barang wajib diisi.',
            'photo.image'          => 'Berkas foto harus berupa gambar.',
            'photo.mimes'          => 'Format foto harus berupa jpg, jpeg, png, atau webp.',
            'photo.max'            => 'Ukuran foto maksimal adalah 2 MB.',
        ];
    }
}
