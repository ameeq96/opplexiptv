<?php

namespace App\Services\Clients;

use App\Models\CheckoutDraft;
use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\CustomerExternalLink;
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
        return User::query()->with('assignedAdmin')->withCount([
            'orders',
            'customerFollowUps as pending_follow_ups_count' => fn ($query) => $query->where('status', 'pending'),
        ]);
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

        if ($request->query('assigned_admin_id') === 'unassigned') {
            $q->whereNull('assigned_admin_id');
        } elseif ($request->filled('assigned_admin_id')) {
            $q->where('assigned_admin_id', (int) $request->query('assigned_admin_id'));
        }

        if (in_array($request->query('subscription'), ['active', 'expired', 'expiring'], true)) {
            $q->whereHas('orders', function ($query) use ($request) {
                $query->where('payment_status', 'paid');
                if ($request->query('subscription') === 'expired') {
                    $query->whereDate('expiry_date', '<', today());
                } elseif ($request->query('subscription') === 'expiring') {
                    $query->where('status', 'active')->whereBetween('expiry_date', [today(), today()->addDays(7)->endOfDay()]);
                } else {
                    $query->where('status', 'active')->where(function ($dates) {
                        $dates->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
                    });
                }
            });
        }

        if (in_array($request->query('payment'), ['paid', 'unpaid', 'test_paid'], true)) {
            $q->whereHas('orders', fn ($query) => $query->where('payment_status', $request->query('payment')));
        }

        if (in_array($request->query('follow_up'), ['pending', 'overdue'], true)) {
            $q->whereHas('customerFollowUps', function ($query) use ($request) {
                $query->where('status', 'pending');
                if ($request->query('follow_up') === 'overdue') {
                    $query->where('due_at', '<', now());
                }
            });
        }

        if ($request->filled('source')) {
            $q->whereHas('customerActivities', fn ($query) => $query->where('source', $request->query('source')));
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

    public function profile(User $client, ?Request $request = null): array
    {
        $request ??= request();
        $client->loadMissing('assignedAdmin');
        $client->loadCount(['orders', 'trialClicks', 'checkoutDrafts', 'digitalOrders']);
        $orders = $client->orders()
            ->with('device')
            ->latest()
            ->paginate(15, ['*'], 'orders_page')->withQueryString();
        $trialClicks = $client->trialClicks()
            ->latest('updated_at')
            ->paginate(15, ['*'], 'leads_page')->withQueryString();
        $checkoutDrafts = $client->checkoutDrafts()
            ->with('completedOrder')
            ->latest('last_activity_at')
            ->paginate(15, ['*'], 'drafts_page')->withQueryString();
        $digitalOrders = $client->digitalOrders()
            ->withCount('items')
            ->latest()
            ->paginate(15, ['*'], 'digital_page')->withQueryString();
        $referrals = Referral::query()
            ->with(['sourceOrder', 'referredOrder'])
            ->where(function ($query) use ($client) {
                $query->where('referrer_user_id', $client->id)
                    ->orWhere('referred_user_id', $client->id);
            })
            ->latest()
            ->paginate(10, ['*'], 'referrals_page')->withQueryString();

        $marketingDeliveries = MarketingDelivery::query()
            ->where(function ($query) use ($client) {
                $query->where('user_id', $client->id)
                    ->orWhereIn('order_id', $client->orders()->select('orders.id'))
                    ->orWhereIn('checkout_draft_id', $client->checkoutDrafts()->select('checkout_drafts.id'));
            })
            ->latest('scheduled_at')
            ->paginate(15, ['*'], 'deliveries_page')->withQueryString();
        $activities = CustomerActivity::query()->where(function ($query) use ($client) {
            $query->where('user_id', $client->id)->orWhere(function ($associated) use ($client) {
                $associated->where('source', 'opplexify')->where('entity_type', 'shared_checkout')
                    ->whereIn('order_id', $client->orders()->select('orders.id'));
            });
        })
            ->with('order:id,type')
            ->when($request->filled('activity_source'), fn ($query) => $query->where('source', $request->query('activity_source')))
            ->latest('occurred_at')->latest('id')
            ->paginate(20, ['*'], 'activity_page')->withQueryString();
        $followUps = $client->customerFollowUps()->with('assignedAdmin')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('due_at')->orderBy('id')
            ->paginate(15, ['*'], 'follow_ups_page')->withQueryString();
        $conversations = $client->customerConversations()->withCount('messages')
            ->latest()->paginate(10, ['*'], 'conversations_page')->withQueryString();
        $conversation = $request->filled('conversation_id')
            ? $client->customerConversations()->findOrFail((int) $request->query('conversation_id'))
            : null;
        $messages = $conversation
            ? CustomerMessage::query()->where('conversation_id', $conversation->id)->orderBy('sent_at')->orderBy('id')
                ->paginate(30, ['*'], 'messages_page')->withQueryString()
            : null;
        $paidTotals = $client->orders()->where('payment_status', 'paid')
            ->selectRaw('COALESCE(paid_currency, currency) as total_currency, SUM(COALESCE(paid_amount, sell_price, price)) as paid_total')
            ->groupByRaw('COALESCE(paid_currency, currency)')->get();
        $admins = Admin::query()->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT])
            ->orderBy('name')->get(['id', 'name', 'role']);
        $activitySources = CustomerActivity::query()->where(function ($query) use ($client) {
            $query->where('user_id', $client->id)->orWhere(function ($associated) use ($client) {
                $associated->where('source', 'opplexify')->where('entity_type', 'shared_checkout')
                    ->whereIn('order_id', $client->orders()->select('orders.id'));
            });
        })->distinct()->orderBy('source')->pluck('source');
        $externalLinks = CustomerExternalLink::query()->where(function ($query) use ($client) {
            $query->where('user_id', $client->id)->orWhere(function ($associated) use ($client) {
                $associated->where('source', 'opplexify')->where('entity_type', 'shared_checkout')
                    ->whereIn('order_id', $client->orders()->select('orders.id'));
            });
        })->with('order:id,type')
            ->latest('updated_at')->latest('id')->paginate(10, ['*'], 'external_page')->withQueryString();

        return compact(
            'orders',
            'trialClicks',
            'checkoutDrafts',
            'digitalOrders',
            'referrals',
            'marketingDeliveries',
            'activities',
            'followUps',
            'conversations',
            'conversation',
            'messages',
            'paidTotals',
            'admins',
            'activitySources',
            'externalLinks'
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
            'customer_activities' => CustomerActivity::query()->whereNull('user_id')
                ->tap(fn ($query) => LandingInteractionService::excludeAnonymousActivities($query))
                ->where(function ($query) {
                    $query->whereNull('entity_type')->orWhere('entity_type', '!=', 'shared_checkout')->orWhereNull('order_id');
                })->count(),
            'customer_conversations' => CustomerConversation::query()->whereNull('user_id')->count(),
        ];
    }
}
