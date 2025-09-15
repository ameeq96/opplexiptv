<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PurchasingQueryService
{
    public function base(): Builder
    {
        return Purchasing::with('pictures');
    }

    public function applySearch(Builder $q, Request $request): void
    {
        if (!$request->filled('search')) return;

        $search = (string) $request->string('search');
        $q->where(function ($qb) use ($search) {
            $qb->where('item_name', 'like', "%{$search}%")
               ->orWhere('cost_price', 'like', "%{$search}%")
               ->orWhere('currency', 'like', "%{$search}%")
               ->orWhere('purchase_date', 'like', "%{$search}%")
               ->orWhere('note', 'like', "%{$search}%");
        });
    }

    public function applySorting(Builder $q): void
    {
        $q->orderBy('id', 'desc');
    }

    public function paginate(Builder $q, Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 10;

        $pager = $q->paginate($perPage);
        $pager->appends($request->all());
        return $pager;
    }
}
