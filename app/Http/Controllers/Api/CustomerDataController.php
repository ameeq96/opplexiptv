<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CustomerDataController extends Controller
{
    public function customers(Request $request): JsonResponse
    {
        $filters = $this->filters($request, [
            'email' => ['sometimes', 'required', 'email', 'max:254'],
            'phone' => ['sometimes', 'required', 'string', 'regex:/^\+?[1-9][0-9]{7,14}$/'],
        ]);
        $query = User::query();
        if (isset($filters['email'])) $query->where('email_normalized', User::normalizeEmail($filters['email']));
        if (isset($filters['phone'])) $query->where('phone_normalized', User::normalizePhone($filters['phone']));

        return $this->listing($query, $filters, fn (User $customer) => $this->customerData($customer));
    }

    public function customer(int $customer): JsonResponse
    {
        return response()->json(['data' => $this->customerData(User::query()->findOrFail($customer))]);
    }

    public function customerOrders(Request $request, int $customer): JsonResponse
    {
        User::query()->findOrFail($customer);
        $filters = $this->orderFilters($request);

        return $this->listing($this->filteredOrders($filters)->where('user_id', $customer), $filters,
            fn (Order $order) => $this->orderData($order));
    }

    public function customerTrials(Request $request, int $customer): JsonResponse
    {
        User::query()->findOrFail($customer);
        $filters = $this->filters($request, ['status' => ['sometimes', 'required', Rule::in(TrialClick::STATUSES)]]);
        $query = TrialClick::query()->where('user_id', $customer)->where('intent', 'trial');
        if (isset($filters['status'])) $query->where('status', $filters['status']);

        return $this->listing($query, $filters, fn (TrialClick $trial) => $this->trialData($trial));
    }

    public function customerConversations(Request $request, int $customer): JsonResponse
    {
        User::query()->findOrFail($customer);
        $filters = $this->conversationFilters($request);

        return $this->listing($this->filteredConversations($filters)->where('user_id', $customer), $filters,
            fn (CustomerConversation $conversation) => $this->conversationData($conversation));
    }

    public function orders(Request $request): JsonResponse
    {
        $filters = $this->orderFilters($request);

        return $this->listing($this->filteredOrders($filters), $filters, fn (Order $order) => $this->orderData($order));
    }

    public function order(int $order): JsonResponse
    {
        return response()->json(['data' => $this->orderData(Order::query()->findOrFail($order))]);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $filters = $this->filters($request, [
            'customer_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'active', 'expired'])],
        ]);
        $query = $this->subscriptionQuery();
        if (isset($filters['customer_id'])) $query->where('user_id', $filters['customer_id']);
        if (isset($filters['status'])) $query->where('status', $filters['status']);

        return $this->listing($query, $filters, fn (Order $order) => $this->subscriptionData($order));
    }

    public function subscription(int $order): JsonResponse
    {
        return response()->json(['data' => $this->subscriptionData($this->subscriptionQuery()->findOrFail($order))]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $filters = $this->conversationFilters($request);

        return $this->listing($this->filteredConversations($filters), $filters,
            fn (CustomerConversation $conversation) => $this->conversationData($conversation));
    }

    public function conversation(int $conversation): JsonResponse
    {
        return response()->json(['data' => $this->conversationData(CustomerConversation::query()->findOrFail($conversation))]);
    }

    public function messages(Request $request, int $conversation): JsonResponse
    {
        CustomerConversation::query()->findOrFail($conversation);
        $filters = $this->filters($request, [
            'sender_type' => ['sometimes', 'required', Rule::in(['customer', 'support', 'agent', 'visitor', 'system'])],
        ]);
        $query = CustomerMessage::query()->where('conversation_id', $conversation);
        if (isset($filters['sender_type'])) $query->where('sender_type', $filters['sender_type']);

        return $this->listing($query, $filters, fn (CustomerMessage $message) => [
            'id' => $message->id,
            'conversation_id' => (int) $message->conversation_id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->sender_name,
            'content' => $message->content,
            'sent_at' => $message->sent_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
            'updated_at' => $message->updated_at?->toIso8601String(),
        ]);
    }

    private function filters(Request $request, array $rules = []): array
    {
        return validator($request->query(), array_merge([
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
            'updated_since' => ['sometimes', 'required', 'date'],
        ], $rules))->validate();
    }

    private function orderFilters(Request $request): array
    {
        return $this->filters($request, [
            'customer_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'type' => ['sometimes', 'required', Rule::in(['package', 'reseller'])],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'active', 'expired'])],
            'payment_status' => ['sometimes', 'required', Rule::in(['unknown', 'unpaid', 'paid', 'test_paid', 'failed', 'refunded'])],
        ]);
    }

    private function filteredOrders(array $filters): Builder
    {
        $query = Order::query();
        if (isset($filters['customer_id'])) $query->where('user_id', $filters['customer_id']);
        foreach (['type', 'status', 'payment_status'] as $field) {
            if (isset($filters[$field])) $query->where($field, $filters[$field]);
        }

        return $query;
    }

    private function subscriptionQuery(): Builder
    {
        return Order::query()->where('type', 'package')->where('payment_status', 'paid');
    }

    private function conversationFilters(Request $request): array
    {
        return $this->filters($request, [
            'customer_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'channel' => ['sometimes', 'required', Rule::in(['portal', 'tawk'])],
            'status' => ['sometimes', 'required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
        ]);
    }

    private function filteredConversations(array $filters): Builder
    {
        $query = CustomerConversation::query();
        if (isset($filters['customer_id'])) $query->where('user_id', $filters['customer_id']);
        foreach (['channel', 'status'] as $field) {
            if (isset($filters[$field])) $query->where($field, $filters[$field]);
        }

        return $query;
    }

    private function listing(Builder $query, array $filters, callable $serialize): JsonResponse
    {
        if (isset($filters['updated_since'])) {
            $query->where('updated_at', '>=', Carbon::parse($filters['updated_since'])->utc());
        }
        $page = $query->orderBy('id')->paginate((int) ($filters['per_page'] ?? 25), ['*'], 'page', (int) ($filters['page'] ?? 1));

        return response()->json([
            'data' => $page->getCollection()->map($serialize)->values()->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    private function customerData(User $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'country' => $customer->country,
            'email_verified_at' => $customer->email_verified_at?->toIso8601String(),
            'renewal_email_enabled' => $customer->renewal_email_enabled,
            'renewal_email_updated_at' => $customer->renewal_email_updated_at?->toIso8601String(),
            'created_at' => $customer->created_at?->toIso8601String(),
            'updated_at' => $customer->updated_at?->toIso8601String(),
        ];
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => $order->id,
            'customer_id' => (int) $order->user_id,
            'type' => $order->type,
            'package_id' => $order->package_id === null ? null : (int) $order->package_id,
            'package' => $order->package,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'price' => number_format((float) $order->price, 2, '.', ''),
            'currency' => $order->currency,
            'paid_amount' => $order->paid_amount,
            'paid_currency' => $order->paid_currency,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'buying_date' => $order->buying_date?->format('Y-m-d'),
            'expiry_date' => $order->expiry_date?->format('Y-m-d'),
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    private function subscriptionData(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_id' => $order->id,
            'customer_id' => (int) $order->user_id,
            'package_id' => $order->package_id === null ? null : (int) $order->package_id,
            'package' => $order->package,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'buying_date' => $order->buying_date?->format('Y-m-d'),
            'expiry_date' => $order->expiry_date?->format('Y-m-d'),
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    private function trialData(TrialClick $trial): array
    {
        return [
            'id' => $trial->id,
            'customer_id' => $trial->user_id === null ? null : (int) $trial->user_id,
            'contact_name' => $trial->contact_name,
            'contact_email' => $trial->contact_email,
            'phone' => $trial->phone_normalized,
            'message' => $trial->message,
            'package_id' => $trial->trial_package_id,
            'package_name' => $trial->package_name,
            'status' => $trial->status,
            'trial_status' => $trial->trial_status,
            'trial_started_at' => $trial->trial_started_at?->toIso8601String(),
            'trial_expires_at' => $trial->trial_expires_at?->toIso8601String(),
            'trial_owner_verified_at' => $trial->trial_owner_verified_at?->toIso8601String(),
            'created_at' => $trial->created_at?->toIso8601String(),
            'updated_at' => $trial->updated_at?->toIso8601String(),
        ];
    }

    private function conversationData(CustomerConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'customer_id' => $conversation->user_id === null ? null : (int) $conversation->user_id,
            'channel' => $conversation->channel,
            'status' => $conversation->status,
            'visitor_name' => $conversation->visitor_name,
            'visitor_email' => $conversation->visitor_email,
            'visitor_phone' => $conversation->visitor_phone,
            'started_at' => $conversation->started_at?->toIso8601String(),
            'ended_at' => $conversation->ended_at?->toIso8601String(),
            'created_at' => $conversation->created_at?->toIso8601String(),
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }
}
