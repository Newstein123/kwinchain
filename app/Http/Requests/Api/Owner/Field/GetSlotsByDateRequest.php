<?php

namespace App\Http\Requests\Api\Owner\Field;

use Illuminate\Foundation\Http\FormRequest;

class GetSlotsByDateRequest extends FormRequest
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
            'date' => ['required', 'date'],
        ];
    }
}
