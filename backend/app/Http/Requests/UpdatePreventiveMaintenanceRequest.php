<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreventiveMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('preventive_maintenances', 'code')->ignore($this->route('maintenance_preventive')?->id),
            ],
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'periodicity' => ['required', 'string', 'in:Mensuel,Trimestriel,Semestriel,Annuel'],
            'last_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:actif,inactif'],
        ];
    }
}
