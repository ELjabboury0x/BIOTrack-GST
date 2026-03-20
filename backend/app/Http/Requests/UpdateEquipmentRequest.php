<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $equipmentId = (int) $this->route('id');

        return [
            'inventory_number_current' => [
                'required',
                'string',
                'max:80',
                Rule::unique('equipments', 'inventory_number_current')->ignore($equipmentId),
            ],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'designation' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'model_name' => ['nullable', 'string', 'max:120'],
            'unit_name' => ['nullable', 'string', 'max:120'],
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
        ];
    }
}
