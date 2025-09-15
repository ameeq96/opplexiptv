<?php

namespace App\Http\Requests\Admin\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchasingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_name'     => 'required|string|max:255',
            'cost_price'    => 'required|numeric',
            'currency'      => 'required|string|max:10',
            'quantity'      => 'required|integer|min:1',
            'purchase_date' => 'nullable|date',
            'screenshots'   => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'note'          => 'nullable|string|max:2000',
        ];
    }
}
