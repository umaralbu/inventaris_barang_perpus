<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteMaintenanceRequest extends FormRequest
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
            'result_status'    => ['required', Rule::in(['success', 'unrepairable'])],
            'to_location_id'   => ['required_if:result_status,success', 'nullable', 'integer', 'exists:locations,id'],
            'completion_date'  => ['required', 'date'],
            'actual_cost'      => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom Indonesian validation messages.
     */
    public function messages(): array
    {
        return [
            'result_status.required'      => 'Hasil perbaikan wajib ditentukan.',
            'to_location_id.required_if'  => 'Lokasi penempatan barang setelah perbaikan wajib dipilih.',
            'completion_date.required'    => 'Tanggal selesai perbaikan wajib diisi.',
            'actual_cost.numeric'         => 'Biaya riil harus berupa angka.',
            'actual_cost.min'             => 'Biaya riil tidak boleh bernilai negatif.',
            'resolution_notes.required'   => 'Catatan hasil perbaikan / penanganan wajib diisi.',
        ];
    }
}

