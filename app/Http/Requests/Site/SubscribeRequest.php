<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => 'required|email'];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('document_ui.validation.required'),
            'email.email' => __('document_ui.validation.email'),
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => __('messages.email'),
        ];
    }
}
