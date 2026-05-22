<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'inventory_number_current' => ['required', 'string', 'max:80', 'unique:equipments,inventory_number_current'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'designation' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'model_name' => ['nullable', 'string', 'max:120'],
            'unit_name' => ['nullable', 'string', 'max:120'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'sector_name' => ['nullable', 'string', 'max:120'],
            'sector_description' => ['nullable', 'string', 'max:255'],
            'market_label' => ['nullable', 'string', 'max:120'],
            'lot_number' => ['nullable', 'string', 'max:120'],
            'article' => ['nullable', 'string', 'max:150'],
            'date_reception_provisoire' => ['nullable', 'date'],
            'duree_garantie' => ['nullable', 'string', 'max:120'],
            'date_reception_definitive' => ['nullable', 'date', 'after_or_equal:date_reception_provisoire'],
            'designation_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'user_manual_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'technical_manual_file' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $serialNumber = trim((string) $this->input('serial_number', ''));

            if ($serialNumber !== '') {
                $serialExists = Equipment::query()
                    ->where('serial_number', $serialNumber)
                    ->exists();

                if ($serialExists) {
                    $validator->errors()->add('serial_number', 'Un équipement avec ce numéro de série existe déjà.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'inventory_number_current.unique' => 'Le numéro inventaire existe déjà.',
        ];
    }
}
