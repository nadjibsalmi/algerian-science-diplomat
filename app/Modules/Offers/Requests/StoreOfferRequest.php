<?php

namespace App\Modules\Offers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'embassy_id' => ['required', 'uuid', 'exists:embassies,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:50000'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'offer_type' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'research_field' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'contract_type' => ['nullable', 'string', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
        ];
    }
}