<?php

namespace App\Http\Requests\Admin\PanelOrders;

use Illuminate\Foundation\Http\FormRequest;

class StorePanelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'               => 'required|exists:users,id',
            'package'               => 'required|string|max:255',
            'price'                 => 'required|numeric',
            'sell_price'            => 'required|numeric|gte:price',
            'status'                => 'required|in:pending,active,expired',
            'currency'              => 'nullable|string|max:10',
            'payment_method'        => 'nullable|string|max:255',
            'custom_payment_method' => 'nullable|string|max:255',
            'custom_package'        => 'nullable|string|max:255',
            'buying_date'           => 'nullable|date',
            'expiry_date'           => 'nullable|date',
            'screenshots'           => 'nullable|array',
            'screenshots.*'         => 'image|max:5120',
            'iptv_username'         => 'nullable|string|max:255',
            'credits'               => 'nullable|integer',
            'duration'              => 'required|integer|min:1',
            'note'                  => 'nullable|string|max:2000',
            'type'                  => 'sometimes|in:reseller,package,all',
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
