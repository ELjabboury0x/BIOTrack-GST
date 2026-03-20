<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'      => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required'      => 'L\'identifiant est obligatoire.',
            'password.required'   => 'Le mot de passe est obligatoire.',
            'service_id.exists'   => 'Le service sélectionné est invalide.',
        ];
    }
}
