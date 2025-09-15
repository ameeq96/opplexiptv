<?php

namespace App\Http\Requests\Admin;


use Illuminate\Foundation\Http\FormRequest;


class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'in:all,today,7days,30days,custom'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
