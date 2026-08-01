<?php

namespace App\Modules\Messaging\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message'        => ['required', 'string', 'min:1', 'max:4000'],
            'attachment_ids' => ['nullable', 'array', 'max:3'],
            'attachment_ids.*' => ['uuid', 'exists:documents,id'],
        ];
    }
}
