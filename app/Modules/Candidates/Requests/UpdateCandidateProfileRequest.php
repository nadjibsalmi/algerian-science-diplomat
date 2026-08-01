<?php

namespace App\Modules\Candidates\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'wilaya' => ['nullable', 'string', 'max:60'],
            'commune' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'national_id' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'current_institution' => ['nullable', 'string', 'max:200'],
            'current_level' => ['nullable', Rule::in(['bac', 'licence', 'master', 'doctorat', 'postdoc', 'professional'])],
            'current_field' => ['nullable', 'string', 'max:200'],
            'current_year' => ['nullable', 'string', 'max:10'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'researchgate_url' => ['nullable', 'url', 'max:255'],
            'orcid' => ['nullable', 'string', 'max:100'],
            'google_scholar_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'personal_website' => ['nullable', 'url', 'max:255'],
            'cover_letter_template' => ['nullable', 'string', 'max:10000'],
            'visibility_settings' => ['nullable', 'array'],
        ];
    }
}