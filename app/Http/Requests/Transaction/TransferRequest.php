<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ItemUnitStatus;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferRequest extends FormRequest
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
            'from_location_id' => ['required', 'integer', 'exists:locations,id'],
            'to_location_id'   => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'staff_id'         => ['required', 'integer', 'exists:staff,id'],
            'transaction_date' => ['required', 'date'],
            'unit_ids'         => ['required', 'array', 'min:1'],
            'unit_ids.*'       => ['required', 'integer', 'distinct', 'exists:items,id'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->from_location_id && !empty($this->unit_ids)) {
                // Ensure all selected units are actually located at from_location_id, active, and 'Tersedia'
                $invalidUnits = Item::whereIn('id', $this->unit_ids)
                    ->where(function ($q) {
                        $q->where('location_id', '!=', $this->from_location_id)
                          ->orWhere('status', '!=', ItemUnitStatus::TERSEDIA->value)
                          ->orWhere('is_active', false);
                    })
                    ->get();

                if ($invalidUnits->isNotEmpty()) {
                    $codes = $invalidUnits->pluck('code')->join(', ');
                    $validator->errors()->add(
                        'unit_ids',
                        "Unit fisik berikut tidak valid untuk dipindahkan (tidak berada di lokasi asal terpilih, atau sedang dipinjam/nonaktif): {$codes}."
                    );
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
            'from_location_id.required'  => 'Lokasi asal barang wajib dipilih.',
            'from_location_id.exists'    => 'Lokasi asal tidak ditemukan.',
            'to_location_id.required'    => 'Lokasi tujuan perpindahan wajib dipilih.',
            'to_location_id.exists'      => 'Lokasi tujuan tidak ditemukan.',
            'to_location_id.different'   => 'Lokasi tujuan harus berbeda dari lokasi asal.',
            'staff_id.required'          => 'Staf penanggung jawab mutasi wajib dipilih.',
            'staff_id.exists'            => 'Staf tidak ditemukan.',
            'transaction_date.required'  => 'Tanggal perpindahan lokasi wajib diisi.',
            'transaction_date.date'      => 'Format tanggal tidak valid.',
            'unit_ids.required'          => 'Wajib memilih minimal satu unit fisik untuk dipindahkan.',
            'unit_ids.min'               => 'Pilih minimal satu unit fisik.',
            'unit_ids.*.distinct'        => 'Terdapat unit fisik yang dipilih duplikat.',
            'notes.max'                  => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
