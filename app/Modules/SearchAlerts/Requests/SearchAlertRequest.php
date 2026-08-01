<?php

namespace App\Modules\SearchAlerts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchAlertRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'filters' => ['required', 'array', 'max:20'],
            'filters.q' => ['nullable', 'string', 'max:200'],
            'filters.country' => ['nullable', 'string', 'max:100'],
            'filters.city' => ['nullable', 'string', 'max:100'],
            'filters.offer_type' => ['nullable', 'string', 'max:100'],
            'filters.category' => ['nullable', 'string', 'max:100'],
            'filters.research_field' => ['nullable', 'string', 'max:150'],
            'filters.level' => ['nullable', 'string', 'max:100'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}