<?php

namespace App\Http\Requests\Admin\Clients;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|unique:users,email',
            'phone'   => 'required|string|unique:users,phone',
            'country' => 'nullable|string|max:100',
        ];
    }
}
