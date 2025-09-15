<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing;
use Illuminate\Http\Request;

class PurchasingCrudService
{
    public function create(Request $request): Purchasing
    {
        $data = collect($request->validated())->only([
            'item_name', 'cost_price', 'currency', 'quantity', 'purchase_date', 'note'
        ])->all();

        return Purchasing::create($data);
    }

    public function update(Request $request, Purchasing $purchasing): void
    {
        $data = collect($request->validated())->only([
            'item_name', 'cost_price', 'currency', 'quantity', 'purchase_date', 'note'
        ])->all();

        $purchasing->update($data);
    }

    public function delete(Purchasing $purchasing): void
    {
        $purchasing->delete();
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        return Purchasing::whereIn('id', $ids)->delete();
    }
}
