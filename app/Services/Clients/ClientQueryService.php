<?php

namespace App\Services\Clients;

use App\Models\CheckoutDraft;
use App\Models\MarketingDelivery;
use App\Models\Referral;
use App\Models\TrialClick;
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

        if (in_array($request->query('consent'), ['email', 'whatsapp', 'ads'], true)) {
            $channel = $request->query('consent');
            $q->whereNotNull("marketing_{$channel}_consented_at")
                ->where(function ($query) use ($channel) {
                    $query->whereNull("marketing_{$channel}_opted_out_at")
                        ->orWhereColumn(
                            "marketing_{$channel}_consented_at",
                            '>',
                            "marketing_{$channel}_opted_out_at"
                        );
                });
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

    public function profile(User $client): array
    {
        $orders = $client->orders()
            ->with('device')
            ->latest()
            ->get();
        $trialClicks = $client->trialClicks()
            ->latest('updated_at')
            ->get();
        $checkoutDrafts = $client->checkoutDrafts()
            ->with('completedOrder')
            ->latest('last_activity_at')
            ->get();
        $digitalOrders = $client->digitalOrders()
            ->withCount('items')
            ->latest()
            ->get();
        $referrals = Referral::query()
            ->with(['sourceOrder', 'referredOrder'])
            ->where(function ($query) use ($client) {
                $query->where('referrer_user_id', $client->id)
                    ->orWhere('referred_user_id', $client->id);
            })
            ->latest()
            ->get();

        $orderIds = $orders->pluck('id');
        $draftIds = $checkoutDrafts->pluck('id');
        $marketingDeliveries = MarketingDelivery::query()
            ->where(function ($query) use ($client, $orderIds, $draftIds) {
                $query->where('user_id', $client->id);
                if ($orderIds->isNotEmpty()) {
                    $query->orWhereIn('order_id', $orderIds);
                }
                if ($draftIds->isNotEmpty()) {
                    $query->orWhereIn('checkout_draft_id', $draftIds);
                }
            })
            ->latest('scheduled_at')
            ->get();

        return compact(
            'orders',
            'trialClicks',
            'checkoutDrafts',
            'digitalOrders',
            'referrals',
            'marketingDeliveries'
        );
    }

    public function unlinkedActivityCounts(): array
    {
        return [
            'trial_clicks' => TrialClick::query()
                ->whereNull('user_id')
                ->whereNotNull('phone_normalized')
                ->count(),
            'checkout_drafts' => CheckoutDraft::query()
                ->whereNull('user_id')
                ->where(function ($query) {
                    $query->whereNotNull('email_normalized')
                        ->orWhereNotNull('phone_normalized');
                })
                ->count(),
        ];
    }
}
