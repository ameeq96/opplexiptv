<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class BuyNowRequest extends FormRequest
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
            'package'  => 'required|string|max:255',
            'phone'    => 'required|string|max:255',
            'message'  => 'required|string',
            'captcha'  => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => __('document_ui.validation.required'),
            'email.email' => __('document_ui.validation.email'),
            '*.string' => __('document_ui.validation.string'),
            '*.max' => __('document_ui.validation.max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => __('messages.form.name'),
            'email' => __('messages.form.email'),
            'package' => __('messages.form.select_package'),
            'phone' => __('messages.form.phone'),
            'message' => __('messages.form.message'),
            'captcha' => 'CAPTCHA',
        ];
    }
}
