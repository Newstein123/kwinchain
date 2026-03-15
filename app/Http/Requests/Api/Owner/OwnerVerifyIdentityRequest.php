<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;

class OwnerVerifyIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nrc_number' => ['required', 'string'],
            'nrc_front_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'nrc_back_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'selfie_with_id' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
