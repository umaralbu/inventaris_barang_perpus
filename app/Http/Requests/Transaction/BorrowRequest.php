<?php

namespace App\Http\Requests\Transaction;

use App\Models\ItemCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BorrowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'catalog_id'           => ['required', 'integer', 'exists:items_catalog,id'],
            'staff_id'             => ['required', 'integer', 'exists:staff,id'],
            'to_location_id'       => ['required', 'integer', 'exists:locations,id'],
            'quantity'             => ['required', 'integer', 'min:1'],
            'transaction_date'     => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:transaction_date'],
            'notes'                => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->catalog_id) {
                $catalog = ItemCatalog::find($this->catalog_id);
                if (!$catalog) {
                    $validator->errors()->add('catalog_id', 'Katalog barang yang dipilih tidak ditemukan.');
                    return;
                }

                if (!$catalog->is_active) {
                    $validator->errors()->add('catalog_id', 'Katalog barang yang dipilih sedang nonaktif.');
                    return;
                }

                $availableCount = $catalog->available_units_count;
                if ($this->quantity && $this->quantity > $availableCount) {
                    $validator->errors()->add('quantity', "Kuantitas yang diminta ({$this->quantity}) melebihi unit tersedia ({$availableCount} unit).");
                }
            }
        });
    }

    /**
     * Custom Indonesian validation messages.
     */
    public function messages(): array
    {
        return [
            'catalog_id.required'           => 'Pilihan barang dari katalog wajib dipilih.',
            'catalog_id.exists'             => 'Katalog barang yang dipilih tidak valid.',
            'staff_id.required'             => 'Staf penanggung jawab / peminjam wajib dipilih.',
            'staff_id.exists'               => 'Staf yang dipilih tidak valid.',
            'to_location_id.required'       => 'Lokasi tujuan penggunaan wajib dipilih.',
            'to_location_id.exists'         => 'Lokasi tujuan tidak valid.',
            'quantity.required'             => 'Jumlah unit yang dipinjam wajib diisi.',
            'quantity.integer'              => 'Jumlah unit harus berupa bilangan bulat.',
            'quantity.min'                  => 'Jumlah unit minimal 1.',
            'transaction_date.required'     => 'Tanggal peminjaman wajib diisi.',
            'transaction_date.date'         => 'Format tanggal peminjaman tidak valid.',
            'expected_return_date.required' => 'Estimasi tanggal pengembalian wajib diisi.',
            'expected_return_date.date'     => 'Format tanggal pengembalian tidak valid.',
            'expected_return_date.after_or_equal' => 'Estimasi tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
            'notes.max'                     => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
