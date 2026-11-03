<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ItemUnitStatus;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id'           => ['required', 'integer', 'exists:items,id'],
            'vendor_technician' => ['required', 'string', 'max:255'],
            'start_date'        => ['required', 'date'],
            'issue_description' => ['required', 'string', 'max:1000'],
            'estimated_cost'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $item = Item::find($this->item_id);
            if ($item && $item->status === ItemUnitStatus::DIPINJAM) {
                $validator->errors()->add('item_id', 'Unit fisik ini sedang dipinjam dan tidak dapat dikirim untuk servis.');
            }
        });
    }

    /**
     * Custom Indonesian validation messages.
     */
    public function messages(): array
    {
        return [
            'item_id.required'           => 'Unit barang fisik wajib dipilih.',
            'item_id.exists'             => 'Unit barang tidak ditemukan.',
            'vendor_technician.required' => 'Nama teknisi / vendor penyedia servis wajib diisi.',
            'start_date.required'        => 'Tanggal mulai servis wajib diisi.',
            'issue_description.required' => 'Deskripsi kerusakan / kendala wajib dijelaskan.',
            'estimated_cost.numeric'     => 'Estimasi biaya harus berupa angka.',
            'estimated_cost.min'         => 'Estimasi biaya tidak boleh bernilai negatif.',
        ];
    }
}

