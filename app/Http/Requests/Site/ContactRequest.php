<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'phone'    => 'required|string|max:255',
            'message'  => 'required|string',
            'captcha'  => 'required',
        ];
    }
}
