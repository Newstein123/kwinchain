<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;

class OwnerRegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:owners,email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
