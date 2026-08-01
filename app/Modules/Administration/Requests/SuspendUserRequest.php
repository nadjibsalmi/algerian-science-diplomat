<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuspendUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'suspended_until' => ['nullable', 'date', 'after:now'],
        ];
    }
}