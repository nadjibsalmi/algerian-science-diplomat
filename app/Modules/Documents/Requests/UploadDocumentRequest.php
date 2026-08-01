<?php

namespace App\Modules\Documents\Requests;

use App\Modules\Documents\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,docx'],
            'type' => ['required', Rule::in(Document::TYPES)],
            'name' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'replaces_id' => ['nullable', 'uuid', Rule::exists('documents', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}