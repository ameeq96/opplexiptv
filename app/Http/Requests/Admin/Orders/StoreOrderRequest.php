<?php

namespace App\Http\Requests\Admin\Orders;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'expiry_date'           => 'nullable|date',
            'buying_date'           => 'required|date',
            'screenshots'           => 'nullable|array',
            'screenshots.*'         => 'image|max:5120',
            'currency'              => 'required|in:PKR,USD,AED,EUR,GBP,SAR,INR,CAD',
            'iptv_username'         => 'nullable|string|max:255',
            'custom_package'        => 'nullable|string|max:255',
            'note'                  => 'nullable|string|max:2000',
            'type'                  => 'sometimes|in:package,reseller,all',
            'tab'                   => 'sometimes|in:unmessaged,messaged,all',
            'date_filter'           => 'sometimes|in:today,yesterday,7days,30days,90days,year',
            'start_date'            => 'sometimes|date',
            'end_date'              => 'sometimes|date|after_or_equal:start_date',
            'expiry_status'         => 'sometimes|in:expired,soon',
            'per_page'              => 'sometimes|integer|min:1|max:200',
            'search'                => 'sometimes|string|max:255',
        ];
    }
}
