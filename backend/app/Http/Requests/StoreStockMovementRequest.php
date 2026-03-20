<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', 'string', 'in:decharge,reception'],
            'part_reference' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'movement_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
