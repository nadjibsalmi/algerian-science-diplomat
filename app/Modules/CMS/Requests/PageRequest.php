<?php

namespace App\Modules\CMS\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($this->route('page'))],
            'template' => ['required', Rule::in(['default', 'landing', 'faq'])],
            'published' => ['sometimes', 'boolean'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', Rule::in(['fr', 'ar', 'en'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.content' => ['required', 'array'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:500'],
            'translations.*.og_image' => ['nullable', 'string', 'max:500'],
        ];
    }
}