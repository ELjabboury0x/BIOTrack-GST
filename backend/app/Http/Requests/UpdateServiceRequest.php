<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('services', 'code')->ignore($service?->id),
            ],
            'name' => ['required', 'string', 'max:120'],
        ];
    }
}
