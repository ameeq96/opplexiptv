<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, OrderPaymentService $payments)
    {
        $secret = (string) config('services.payment_webhook.secret');
        if ($secret === '') {
            return response()->json(['message' => 'Payment webhook is not configured.'], 503);
        }
        $allowedProviders = config('services.payment_webhook.providers', []);
        if ($allowedProviders === []) {
            return response()->json(['message' => 'Payment adapter allowlist is not configured.'], 503);
        }

        $timestamp = (string) $request->header('X-Opplex-Timestamp', '');
        $signature = (string) $request->header('X-Opplex-Signature', '');
        $signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $tolerance = max(30, (int) config('services.payment_webhook.tolerance_seconds', 300));

        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > $tolerance) {
            return response()->json(['message' => 'Expired or invalid timestamp.'], 401);
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);
        if ($signature === '' || !hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $validator = Validator::make($request->json()->all(), [
            'order_id' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:paid,settled'],
            'amount' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/'],
            'currency' => ['required', 'string', 'size:3'],
            'provider' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'transaction_id' => ['required', 'string', 'max:191'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid payload.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['provider'] = strtolower($data['provider']);
        $data['currency'] = strtoupper($data['currency']);
        if (!in_array($data['provider'], array_map('strtolower', $allowedProviders), true)) {
            return response()->json(['message' => 'Payment adapter is not allowed.'], 403);
        }
        try {
            $result = DB::transaction(function () use ($data, $payments) {
            $order = Order::query()->lockForUpdate()->find($data['order_id']);
            if (!$order) {
                return ['status' => 404, 'message' => 'Order not found.'];
            }

            if ($order->payment_status === 'paid') {
                $matches = $order->payment_provider === $data['provider']
                    && $order->provider_transaction_id === $data['transaction_id'];

                return $matches
                    ? ['status' => 200, 'message' => 'Payment already recorded.', 'order_id' => $order->id]
                    : ['status' => 409, 'message' => 'Order is already paid with another transaction.'];
            }

            $expectedCents = (int) round((float) ($order->sell_price ?? $order->price ?? 0) * 100);
            $receivedCents = (int) round((float) $data['amount'] * 100);
            if ($expectedCents !== $receivedCents
                || strtoupper((string) $order->currency) !== $data['currency']) {
                return ['status' => 409, 'message' => 'Amount or currency mismatch.'];
            }

            $duplicate = Order::query()
                ->where('payment_provider', $data['provider'])
                ->where('provider_transaction_id', $data['transaction_id'])
                ->first();

            if ($duplicate && $duplicate->id !== $order->id) {
                return ['status' => 409, 'message' => 'Transaction already belongs to another order.'];
            }

            $payments->markPaid(
                $order,
                $data['provider'],
                $data['transaction_id'],
                (float) $data['amount'],
                $data['currency']
            );

            return ['status' => 200, 'message' => 'Payment recorded.', 'order_id' => $order->id];
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) !== '23000') {
                throw $exception;
            }

            return response()->json(['message' => 'Transaction already recorded.'], 409);
        }

        $status = $result['status'];
        unset($result['status']);

        return response()->json($result, $status);
    }
}
