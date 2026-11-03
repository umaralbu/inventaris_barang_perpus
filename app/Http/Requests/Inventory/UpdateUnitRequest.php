<?php

namespace App\Http\Requests\Inventory;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $unitId = $this->route('unit') ? $this->route('unit')->id : null;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'code')->ignore($unitId),
            ],
            'location_id'    => ['required', 'exists:locations,id'],
            'condition'      => ['required', new Enum(ItemCondition::class)],
            'status'         => ['required', new Enum(ItemUnitStatus::class)],
            'purchase_date'  => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'        => 'Kode serial unit fisik wajib diisi.',
            'code.unique'          => 'Kode serial ini sudah digunakan oleh unit lain.',
            'location_id.required' => 'Lokasi ruangan/rak wajib dipilih.',
            'location_id.exists'   => 'Lokasi yang dipilih tidak valid.',
            'condition.required'   => 'Kondisi fisik unit wajib ditentukan.',
            'status.required'      => 'Status ketersediaan unit wajib ditentukan.',
        ];
    }
}

