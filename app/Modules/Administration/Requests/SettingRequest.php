<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'value' => ['required', 'array'],
            'group' => ['sometimes', 'string', 'max:80'],
        ];
    }
}