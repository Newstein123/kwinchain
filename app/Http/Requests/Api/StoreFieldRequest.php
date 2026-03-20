<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'min:3', 'max:150'],
            'location'        => ['required', 'string', 'max:100'],
            'price_per_hour'  => ['required', 'numeric', 'min:0'],
            'deposit_amount'  => ['required', 'numeric', 'min:0'],
            'description'     => ['nullable', 'string'],
            'image'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
