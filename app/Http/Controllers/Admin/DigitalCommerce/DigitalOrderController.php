<?php

namespace App\Http\Controllers\Admin\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Mail\DigitalOrderPlacedMail;
use App\Models\Digital\DigitalDeliveryPayload;
use App\Models\Digital\DigitalOrder;
use App\Models\Digital\DigitalOrderItem;
use App\Services\DigitalCommerce\DeliveryAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class DigitalOrderController extends Controller
{
    public function __construct(
        private readonly DeliveryAssignmentService $assignmentService,
    ) {
    }

    public function index(Request $request)
    {
        Gate::forUser(auth('admin')->user())->authorize('manage-digital-commerce');

        $status = (string) $request->query('status', '');
        $paymentStatus = (string) $request->query('payment_status', '');
        $q = trim((string) $request->query('q', ''));

        $orders = DigitalOrder::query()
            ->withCount('items')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($paymentStatus !== '', fn ($query) => $query->where('payment_status', $paymentStatus))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.digital-orders.index', compact('orders', 'status', 'paymentStatus', 'q'));
    }

    public function show(DigitalOrder $digital_order)
    {
        Gate::forUser(auth('admin')->user())->authorize('viewAdmin', $digital_order);

        $digital_order->load([
            'items.product:id,title',
            'items.deliveryPayload:id,payload_type,payload,is_assigned',
            'items.assignedBy:id,name',
        ]);

        $itemIds = $digital_order->items->pluck('id')->all();
        $availablePayloads = DigitalDeliveryPayload::query()
            ->where('is_assigned', false)
            ->whereIn('digital_product_id', $digital_order->items->pluck('digital_product_id')->filter()->unique()->all())
            ->orderBy('id')
            ->get()
            ->groupBy('digital_product_id');

        return view('admin.digital-orders.show', compact('digital_order', 'availablePayloads', 'itemIds'));
    }

    public function markPaid(DigitalOrder $digital_order)
    {
        Gate::forUser(auth('admin')->user())->authorize('markPaid', $digital_order);

        $digital_order->update([
            'payment_status' => 'paid',
            'status' => in_array($digital_order->status, ['pending', 'paid'], true) ? 'paid' : $digital_order->status,
            'paid_at' => now(),
        ]);

        foreach ($digital_order->items as $item) {
            if (!$item->delivery_payload_id) {
                try {
                    $this->assignmentService->assignFirstAvailable($item, auth('admin')->user());
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return back()->with('success', 'Order marked as paid. Auto-assignment attempted.');
    }

    public function markDelivered(DigitalOrder $digital_order)
    {
        Gate::forUser(auth('admin')->user())->authorize('updateAdmin', $digital_order);

        $digital_order->items()->whereNotNull('delivery_payload_id')->update([
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $digital_order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return back()->with('success', 'Order marked as delivered.');
    }

    public function assignDelivery(Request $request, DigitalOrder $digital_order, DigitalOrderItem $item)
    {
        Gate::forUser(auth('admin')->user())->authorize('assignDelivery', $digital_order);

        abort_if($item->digital_order_id !== $digital_order->id, 404);

        $payloadId = (int) $request->validate([
            'payload_id' => ['required', 'integer', 'exists:digital_delivery_payloads,id'],
        ])['payload_id'];

        $payload = DigitalDeliveryPayload::query()->findOrFail($payloadId);

        try {
            $this->assignmentService->assignManual($item, $payload, auth('admin')->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Delivery payload assigned.');
    }

    public function resendEmail(DigitalOrder $digital_order)
    {
        Gate::forUser(auth('admin')->user())->authorize('updateAdmin', $digital_order);

        try {
            Mail::to($digital_order->customer_email)->queue(new DigitalOrderPlacedMail($digital_order->fresh('items'), false));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Unable to queue email right now.');
        }

        return back()->with('success', 'Order email queued.');
    }
}
