<?php

namespace App\Services\Clients;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientQueryService
{
    public function base(): Builder
    {
        return User::query();
    }

    public function applyFilters(Builder $q, Request $request): void
    {
        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $q->where(function ($qb) use ($search) {
                $qb->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('exclude_iptv')) {
            $q->where('name', 'not like', '%iptv%');
        }
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
