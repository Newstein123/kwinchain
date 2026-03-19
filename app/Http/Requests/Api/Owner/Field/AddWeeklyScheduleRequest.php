<?php

namespace App\Http\Requests\Api\Owner\Field;

use Illuminate\Foundation\Http\FormRequest;

class AddWeeklyScheduleRequest extends FormRequest
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
            'day_of_week' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }
}
