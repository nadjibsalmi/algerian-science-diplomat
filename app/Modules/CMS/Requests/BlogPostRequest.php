<?php

namespace App\Modules\CMS\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('blog_posts', 'slug')->ignore($this->route('post'))],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'tags' => ['nullable', 'array', 'max:30'],
            'category' => ['nullable', 'string', 'max:100'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', Rule::in(['fr', 'ar', 'en'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.content' => ['required', 'array'],
            'translations.*.excerpt' => ['nullable', 'string', 'max:500'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}