<?php

namespace App\Modules\Candidates\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'offer_id' => [
                'required',
                'uuid',
                Rule::exists('offers', 'id')->where(
                    fn ($query) => $query->where('status', 'published')->where('visibility', 'public')
                ),
            ],
        ];
    }
}