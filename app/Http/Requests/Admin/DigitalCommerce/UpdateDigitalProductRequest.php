<?php

namespace App\Http\Requests\Admin\DigitalCommerce;

use App\Models\Digital\DigitalProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDigitalProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        /** @var DigitalProduct $product */
        $product = $this->route('digital_product');

        return [
            'digital_category_id' => ['nullable', 'integer', 'exists:digital_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('digital_products', 'slug')->ignore($product?->id)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'full_description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'delivery_type' => ['required', Rule::in(['credential', 'code', 'link', 'file', 'manual'])],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable', 'string', 'max:255'],
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
