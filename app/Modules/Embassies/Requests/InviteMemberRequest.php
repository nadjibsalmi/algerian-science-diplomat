<?php

namespace App\Modules\Embassies\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'role_in_embassy' => [
                'required',
                Rule::in(['director', 'recruiter', 'hr']),
            ],
        ];
    }
}