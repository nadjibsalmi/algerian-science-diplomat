<?php

namespace App\Modules\Analytics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_.-]+$/'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'uuid'],
            'properties' => ['nullable', 'array', 'max:30'],
        ];
    }
}