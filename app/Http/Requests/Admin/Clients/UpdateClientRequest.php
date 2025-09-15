<?php

namespace App\Http\Requests\Admin\Clients;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('client')?->id ?? $this->route('id');

        return [
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|unique:users,email,' . $id,
            'phone'   => 'required|string|unique:users,phone,' . $id,
            'country' => 'nullable|string|max:100',
        ];
    }
}
