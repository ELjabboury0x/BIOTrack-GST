<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSparePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $entryMode = $this->input('entry_mode');
        $phase = $this->input('phase', 'decharge');
        $isFormMode = $entryMode === 'form';

        return [
            'phase' => ['required', Rule::in(['decharge', 'retour'])],
            'entry_mode' => ['required', Rule::in(['pdf', 'form'])],
            'document_pdf' => [Rule::requiredIf($entryMode === 'pdf'), 'nullable', 'file', 'mimes:pdf', 'max:15360'],

            'code' => [
                $isFormMode ? 'required' : 'nullable',
                'string',
                'max:80',
                Rule::unique('spare_parts', 'code')->ignore($this->route('piece')?->id),
            ],
            'name' => [$isFormMode ? 'required' : 'nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => [$isFormMode ? 'required' : 'nullable', 'integer', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],

            'discharge_date' => [Rule::requiredIf($isFormMode && $phase === 'decharge'), 'nullable', 'date'],
            'serial_number' => [Rule::requiredIf($isFormMode && $phase === 'decharge'), 'nullable', 'string', 'max:190'],
            'action_user_id' => [Rule::requiredIf($isFormMode && $phase === 'decharge'), 'nullable', 'integer', 'exists:users,id'],
            'assistant_technician_id' => [Rule::requiredIf($isFormMode && $phase === 'decharge'), 'nullable', 'integer', 'exists:users,id'],
            'service_id' => [Rule::requiredIf($isFormMode), 'nullable', 'integer', 'exists:services,id'],
            'major_signer_id' => [Rule::requiredIf($isFormMode && $phase === 'decharge'), 'nullable', 'integer', 'exists:users,id'],

            'return_date' => [Rule::requiredIf($isFormMode && $phase === 'retour'), 'nullable', 'date'],
            'condition_state' => [Rule::requiredIf($isFormMode && $phase === 'retour'), 'nullable', Rule::in(['neuf', 'repare', 'hs'])],
            'return_technician_id' => [Rule::requiredIf($isFormMode && $phase === 'retour'), 'nullable', 'integer', 'exists:users,id'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $mode = $this->input('entry_mode');
        if (!$mode) {
            $mode = $this->hasFile('document_pdf') ? 'pdf' : 'form';
        }

        $this->merge([
            'entry_mode' => $mode,
            'phase' => $this->input('phase', 'decharge'),
        ]);
    }
}
