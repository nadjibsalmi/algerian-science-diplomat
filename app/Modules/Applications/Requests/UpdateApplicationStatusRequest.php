<?php

namespace App\Modules\Applications\Requests;

use App\Modules\Applications\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Application::STATUSES)],
            'note'   => ['nullable', 'string', 'max:500'],
        ];
    }
}
