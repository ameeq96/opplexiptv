<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'               => 'required|exists:users,id',
            'package'               => 'required',
            'price'                 => 'required|numeric',
            'duration'              => 'nullable|integer',
            'status'                => 'required|in:pending,active,expired',
            'payment_method'        => 'nullable|string|max:255',
            'custom_payment_method' => 'nullable|string|max:255',
            'currency'              => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
            'buying_date'           => 'required|date',
            'expiry_date'           => 'nullable|date',
            'screenshots'           => 'nullable|array',
            'screenshots.*'         => 'image|max:5120',
            'custom_package'        => 'nullable|string|max:255',
            'iptv_username'         => 'nullable|string|max:255',
            'note'                  => 'nullable|string|max:2000',
        ];
    }
}
