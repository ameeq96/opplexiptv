<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Order;
use App\Notifications\NewOrderNotification;
use App\Services\Orders\OrderMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class CustomerOrderStatusController extends Controller
{
    public function __construct(private OrderMediaService $media)
    {
    }

    public function show(Request $request, int $orderId): Response|JsonResponse
    {
        $order = Order::query()->findOrFail($orderId);
        $this->setOrderLocale($order);

        if (! $request->routeIs('api.browser.*')) {
            $summary = $request->session()->get('order_summary');
            if (is_array($summary) && (int) ($summary['id'] ?? 0) === $orderId) {
                $request->session()->put('customer_status_context.'.$orderId, [
                    'summary' => $summary, 'checkout_completed' => (bool) session('checkout_completed', false),
                    'message' => session('success'), 'expires_at' => now()->addMinutes(5)->timestamp,
                ]);
            }
            $statusApiUrl = URL::signedRoute('api.browser.orders.status', ['orderId' => $orderId],
                $request->query('expires') ? \Illuminate\Support\Carbon::createFromTimestamp((int) $request->query('expires')) : null);
            return response()->view('pages.checkout.thank-you', compact('statusApiUrl'))->withHeaders([
                'Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache',
                'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }
        $order->load('pictures');

        $context = $request->session()->pull('customer_status_context.'.$orderId, []);
        if (! is_array($context) || (int) ($context['expires_at'] ?? 0) <= now()->timestamp) $context = [];
        $sessionSummary = $context['summary'] ?? session('order_summary', []);
        if (! is_array($sessionSummary)
            || (int) ($sessionSummary['id'] ?? 0) !== (int) $order->id) {
            $sessionSummary = [];
        }
        $orderSummary = array_merge([
            'id' => $order->id,
            'package_id' => $order->package_id,
            'package' => $order->package,
            'package_type' => $order->type,
            'vendor' => null,
            'device' => null,
            'subtotal' => $order->subtotal ?? $order->sell_price ?? $order->price,
            'discount' => $order->discount ?? 0,
            'promotion_name' => null,
            'total' => $order->sell_price ?? $order->price,
            'currency' => $order->currency ?: 'USD',
            'payment_method' => $order->payment_method,
        ], $sessionSummary);

        $proofSubmittedAt = $order->pictures->max('created_at');
        $paymentProofUploadUrl = null;

        if ($order->status === 'pending'
            && $order->payment_status !== 'paid'
            && $order->pictures->count() < 3) {
            $paymentProofUploadUrl = URL::temporarySignedRoute(
                'api.browser.orders.payment-proof',
                \Illuminate\Support\Carbon::createFromTimestamp(min(now()->addMinutes(30)->timestamp, (int) ($request->query('expires') ?: PHP_INT_MAX))),
                ['orderId' => $order->getKey()]
            );
        }

        return response()->json(['data' => [
            'id' => $order->id, 'status' => $order->status, 'payment_status' => $order->payment_status,
            'created_at' => $order->created_at?->toIso8601String(), 'paid_at' => $order->payment_status === 'paid' ? $order->paid_at?->toIso8601String() : null,
            'summary' => array_intersect_key($orderSummary, array_flip(['id', 'package_id', 'package', 'package_type', 'vendor', 'device', 'subtotal', 'discount', 'promotion_name', 'total', 'currency', 'payment_method'])),
            'proof_count' => $order->pictures->count(), 'proof_submitted_at' => $proofSubmittedAt?->toIso8601String(),
            'proof_upload_url' => $paymentProofUploadUrl,
            'checkout_completed' => (bool) ($context['checkout_completed'] ?? session('checkout_completed', false)),
            'message' => is_string($context['message'] ?? null) ? $context['message'] : null,
            'whatsapp_number' => preg_replace('/\D+/', '', (string) config('services.whatsapp.number')),
            'locale' => app()->getLocale(), 'labels' => __('messages.thankyou_page'),
            'order_number_label' => __('interface.email.checkout.order_number'),
        ]])->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    public function storeProof(Request $request, int $orderId): RedirectResponse|JsonResponse
    {
        $order = Order::query()->findOrFail($orderId);
        $this->setOrderLocale($order);

        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($data, $order): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending' || $lockedOrder->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'payment_proof' => __('messages.thankyou_page.proof_not_allowed'),
                ]);
            }

            if ($lockedOrder->pictures()->count() >= 3) {
                throw ValidationException::withMessages([
                    'payment_proof' => __('messages.thankyou_page.proof_limit'),
                ]);
            }

            $this->media->storeScreenshots($lockedOrder, [$data['payment_proof']]);
        }, 3);

        try {
            $admins = Admin::query()
                ->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT])
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewOrderNotification([
                    'title' => 'Payment proof uploaded',
                    'body' => "Payment proof received for order #{$order->id}.",
                    'order_id' => $order->id,
                    'package' => $order->package,
                    'type' => $order->type,
                    'payment' => $order->payment_method,
                    'price' => $order->sell_price ?? $order->price,
                    'created' => now()->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to notify admins about a payment proof', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }

        if ($request->routeIs('api.browser.*')) return response()->json(['message' => __('messages.thankyou_page.proof_received'), 'redirect_url' => $this->statusUrl($order)]);
        return redirect()->to($this->statusUrl($order))
            ->with('success', __('messages.thankyou_page.proof_received'));
    }

    private function setOrderLocale(Order $order): void
    {
        $supportedLocales = array_keys((array) config('laravellocalization.supportedLocales', []));

        if (in_array($order->locale, $supportedLocales, true)) {
            app()->setLocale($order->locale);
        }
    }

    private function statusUrl(Order $order): string
    {
        return URL::temporarySignedRoute(
            'orders.status',
            now()->addDays(90),
            ['orderId' => $order->getKey()]
        );
    }
}
