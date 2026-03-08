<?php

namespace App\Http\Requests\Admin\DigitalCommerce;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryPayloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'digital_product_id' => ['required', 'integer', 'exists:digital_products,id'],
            'payload_type' => ['required', Rule::in(['credential', 'code', 'link', 'file', 'manual'])],
            'payload.username' => ['nullable', 'string', 'max:255'],
            'payload.email' => ['nullable', 'email', 'max:255'],
            'payload.password' => ['nullable', 'string', 'max:255'],
            'payload.code' => ['nullable', 'string', 'max:255'],
            'payload.url' => ['nullable', 'url', 'max:500'],
            'payload.file_url' => ['nullable', 'url', 'max:500'],
            'payload.note' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
