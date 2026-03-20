<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'code' => ['required', 'string', 'max:40', 'unique:services,code'],
            'name' => ['required', 'string', 'max:120'],
        ];
    }
}
