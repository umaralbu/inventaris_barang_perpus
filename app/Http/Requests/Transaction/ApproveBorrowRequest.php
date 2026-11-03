<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveBorrowRequest extends FormRequest
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
            'unit_ids'   => ['required', 'array'],
            'unit_ids.*' => ['required', 'integer', 'distinct', 'exists:items,id'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $transaction = $this->route('transaction');
            if (!$transaction) {
                return;
            }

            if ($this->unit_ids && is_array($this->unit_ids)) {
                if (count($this->unit_ids) !== (int) $transaction->quantity) {
                    $validator->errors()->add(
                        'unit_ids',
                        "Jumlah unit fisik yang dipilih (" . count($this->unit_ids) . ") harus sama dengan jumlah permintaan (" . $transaction->quantity . " unit)."
                    );
                }

                // Verify units belong to the catalog, are available, active, and in good condition
                $invalidUnits = Item::whereIn('id', $this->unit_ids)
                    ->where(function ($query) use ($transaction) {
                        if ($transaction->catalog_id) {
                            $query->where('catalog_id', '!=', $transaction->catalog_id);
                        }
                        $query->orWhere('status', '!=', ItemUnitStatus::TERSEDIA->value)
                              ->orWhere('condition', '!=', ItemCondition::BAIK->value)
                              ->orWhere('is_active', false);
                    })
                    ->get();

                if ($invalidUnits->isNotEmpty()) {
                    $codes = $invalidUnits->pluck('code')->join(', ');
                    $validator->errors()->add(
                        'unit_ids',
                        "Unit berikut tidak valid untuk dipinjamkan (bukan katalog terkait, sedang dipinjam/rusak/nonaktif): {$codes}."
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
            'unit_ids.required'   => 'Wajib memilih unit fisik yang akan dialokasikan.',
            'unit_ids.array'      => 'Format data unit fisik tidak valid.',
            'unit_ids.*.required' => 'Unit fisik tidak boleh kosong.',
            'unit_ids.*.integer'  => 'ID unit fisik harus berupa angka.',
            'unit_ids.*.distinct' => 'Terdapat unit fisik yang dipilih ganda.',
            'unit_ids.*.exists'   => 'Unit fisik yang dipilih tidak ditemukan dalam sistem.',
            'notes.max'           => 'Catatan maksimal 1000 karakter.',
        ];
    }
}

