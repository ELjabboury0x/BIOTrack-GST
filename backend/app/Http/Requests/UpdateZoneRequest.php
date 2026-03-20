<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $zone = $this->route('zone');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('zones', 'name')->ignore($zone?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
