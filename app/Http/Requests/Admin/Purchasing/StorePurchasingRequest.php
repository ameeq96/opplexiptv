<?php

namespace App\Http\Requests\Admin\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchasingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

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
            'search'        => 'sometimes|string|max:255',
            'per_page'      => 'sometimes|integer|min:1|max:200',
        ];
    }
}
