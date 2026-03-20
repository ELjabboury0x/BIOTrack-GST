<?php

namespace App\Http\Requests;

use App\Services\AppSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateOwnPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $settings = app(AppSettingsService::class);
        $minLength = max(8, min(32, $settings->int('password_min_length', 12)));

        $passwordRule = Password::min($minLength)->letters();

        if ($settings->bool('require_uppercase', true)) {
            $passwordRule = $passwordRule->mixedCase();
        }

        if ($settings->bool('require_numbers', true)) {
            $passwordRule = $passwordRule->numbers();
        }

        if ($settings->bool('require_symbols', true)) {
            $passwordRule = $passwordRule->symbols();
        }

        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                $passwordRule,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
        ];
    }
}
