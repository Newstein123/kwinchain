<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;

class OwnerVerifyBusinessRequest extends FormRequest
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
            'business_name' => ['required', 'string'],
            'business_license_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'field_location_lat' => ['required', 'numeric'],
            'field_location_lng' => ['required', 'numeric'],
            'utility_bill_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }
}
