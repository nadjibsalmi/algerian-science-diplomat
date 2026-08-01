<?php

namespace App\Modules\Applications\Requests;

use App\Modules\Documents\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cover_letter'    => ['nullable', 'string', 'max:5000'],
            'document_ids'    => ['nullable', 'array'],
            'document_ids.*'  => [
                'uuid',
                Rule::exists('documents', 'id')->where('user_id', $this->user()->id)
                    ->where('status', Document::STATUS_CLEAN),
            ],
            'answers'         => ['nullable', 'array'],
            'answers.*.question_id' => ['required_with:answers', 'uuid'],
            'answers.*.answer'      => ['required_with:answers', 'string', 'max:2000'],
        ];
    }
}
