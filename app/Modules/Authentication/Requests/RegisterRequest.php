<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname'       => ['required', 'string', 'max:100'],
            'lastname'        => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'phone'           => ['nullable', 'string', 'max:20'],
            'preferred_language' => ['nullable', 'string', 'in:fr,ar,en'],
            'terms_accepted'  => ['required', 'accepted'],
            'privacy_accepted' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted'   => __('validation.terms_required'),
            'privacy_accepted.accepted' => __('validation.privacy_required'),
        ];
    }
}
