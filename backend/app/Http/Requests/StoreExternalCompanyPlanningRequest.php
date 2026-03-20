<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExternalCompanyPlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'planned_date' => ['required', 'date'],
            'planned_date_end' => ['nullable', 'date', 'after_or_equal:planned_date'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:en_attente,en_cours,termine'],
        ];
    }
}
