<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $login = (string) $this->input('login', '');
        $login = str_replace(["\u{00A0}", "\u{2007}", "\u{202F}"], ' ', $login);
        $login = preg_replace('/^\s+|\s+$/u', '', $login);

        $this->merge([
            'login' => (string) ($login ?? ''),
        ]);
    }

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
