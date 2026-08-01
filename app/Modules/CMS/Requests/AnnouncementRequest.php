<?php

namespace App\Modules\CMS\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['info', 'warning', 'success', 'danger'])],
            'message' => ['required', 'array'],
            'message.fr' => ['required', 'string', 'max:1000'],
            'message.ar' => ['nullable', 'string', 'max:1000'],
            'message.en' => ['nullable', 'string', 'max:1000'],
            'link' => ['nullable', 'url', 'max:500'],
            'active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}