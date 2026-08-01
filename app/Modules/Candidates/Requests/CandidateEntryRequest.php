<?php

namespace App\Modules\Candidates\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return match ($this->route('section')) {
            'education' => [
                'institution' => ['required', 'string', 'max:255'],
                'degree' => ['required', 'string', 'max:100'],
                'field' => ['required', 'string', 'max:255'],
                'grade' => ['nullable', 'string', 'max:100'],
                'start_year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
                'end_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 10), 'gte:start_year'],
                'current' => ['boolean'],
                'description' => ['nullable', 'string', 'max:5000'],
            ],
            'experience' => [
                'title' => ['required', 'string', 'max:255'],
                'company' => ['required', 'string', 'max:255'],
                'location' => ['nullable', 'string', 'max:255'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'current' => ['boolean'],
                'description' => ['nullable', 'string', 'max:5000'],
            ],
            'language' => [
                'language' => ['required', 'string', 'max:10'],
                'level' => ['required', Rule::in(['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'native'])],
            ],
            'skill' => [
                'name' => ['required', 'string', 'max:255'],
                'category' => ['nullable', Rule::in(['tech', 'soft', 'tool', 'other'])],
            ],
            'award' => [
                'title' => ['required', 'string', 'max:255'],
                'issuer' => ['nullable', 'string', 'max:255'],
                'year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
                'description' => ['nullable', 'string', 'max:5000'],
            ],
            'publication' => [
                'title' => ['required', 'string', 'max:255'],
                'journal' => ['nullable', 'string', 'max:255'],
                'year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
                'doi' => ['nullable', 'string', 'max:255'],
                'url' => ['nullable', 'url', 'max:255'],
                'type' => ['required', Rule::in(['article', 'conference', 'book', 'thesis', 'patent'])],
            ],
            default => [],
        };
    }
}