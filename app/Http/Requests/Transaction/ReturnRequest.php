<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ItemCondition;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReturnRequest extends FormRequest
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
            'borrow_transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'return_location_id'    => ['required', 'integer', 'exists:locations,id'],
            'staff_id'              => ['required', 'integer', 'exists:staff,id'],
            'return_date'           => ['required', 'date'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'units'                 => ['required', 'array', 'min:1'],
            'units.*.item_id'       => ['required', 'integer', 'exists:items,id'],
            'units.*.selected'      => ['nullable'],
            'units.*.condition'     => ['nullable', Rule::in(array_column(ItemCondition::cases(), 'value'))],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $borrowTx = Transaction::find($this->borrow_transaction_id);

            if (!$borrowTx) {
                return;
            }

            // Only Dipinjam or Sebagian Dikembalikan can be returned
            if (!in_array($borrowTx->status, [TransactionStatus::DIPINJAM, TransactionStatus::SEBAGIAN_DIKEMBALIKAN])) {
                $validator->errors()->add(
                    'borrow_transaction_id',
                    "Transaksi {$borrowTx->transaction_code} tidak dapat dikembalikan karena berstatus {$borrowTx->status->value}."
                );
                return;
            }

            // Authorization: Non-admin can only return their own transactions
            if (!auth()->user()->isAdmin() && $borrowTx->user_id !== auth()->id()) {
                $validator->errors()->add('borrow_transaction_id', 'Anda tidak berhak memproses pengembalian untuk transaksi ini.');
                return;
            }

            // Check how many units are checked
            $selectedUnits = collect($this->units)->filter(fn ($u) => !empty($u['selected']));

            if ($selectedUnits->isEmpty()) {
                $validator->errors()->add('units', 'Pilih minimal satu unit fisik yang ingin dikembalikan.');
                return;
            }

            // Check if any selected unit lacks condition
            foreach ($selectedUnits as $u) {
                if (empty($u['condition'])) {
                    $validator->errors()->add('units', 'Kondisi fisik wajib dipilih untuk setiap unit yang dikembalikan.');
                    break;
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
            'borrow_transaction_id.required' => 'Transaksi peminjaman asal wajib dipilih.',
            'borrow_transaction_id.exists'   => 'Transaksi peminjaman tidak ditemukan.',
            'return_location_id.required'    => 'Lokasi rak/ruangan pengembalian wajib dipilih.',
            'return_location_id.exists'      => 'Lokasi pengembalian tidak valid.',
            'staff_id.required'              => 'Staf penanggung jawab pengembalian wajib dipilih.',
            'staff_id.exists'                => 'Staf tidak valid.',
            'return_date.required'           => 'Tanggal pengembalian wajib diisi.',
            'return_date.date'               => 'Format tanggal pengembalian tidak valid.',
            'units.required'                 => 'Rincian unit fisik yang dikembalikan tidak boleh kosong.',
            'units.min'                      => 'Minimal satu unit fisik harus dikembalikan.',
            'notes.max'                      => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
