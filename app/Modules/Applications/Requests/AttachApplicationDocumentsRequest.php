<?php

namespace App\Modules\Applications\Requests;

use App\Modules\Documents\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachApplicationDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => [
                'required', 'uuid',
                Rule::exists('documents', 'id')->where('user_id', $this->user()->id)->where('status', Document::STATUS_CLEAN),
            ],
        ];
    }
}