<?php

namespace App\Http\Requests\WorkSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_workday' => ['required', 'boolean'],
            'days.*.entry' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'days.*.lunch_start' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'days.*.lunch_end' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'days.*.exit' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
        ];
    }
}
