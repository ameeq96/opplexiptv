<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use App\Models\Order;
use App\Services\LandingExperienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerReviewController extends Controller
{
    public function show(Request $request, Order $order, LandingExperienceService $experience)
    {
        $this->owned($request, $order);
        $brand = $experience->reviewBrand($order);
        $review = CustomerReview::query()->where('order_id', $order->id)->where('user_id', $order->user_id)->first();

        return response()->json(['data' => $review ? $this->data($review) : null, 'eligible' => $brand !== null, 'brand' => $brand]);
    }

    public function store(Request $request, Order $order, LandingExperienceService $experience)
    {
        $this->owned($request, $order);
        $plain = function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (trim($value) === '' || strip_tags($value) !== $value || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value))) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $data = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:80', $plain],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000', $plain],
            'locale' => ['required', 'string', Rule::in(config('app.locales', ['en']))],
            'publication_consent' => ['required', 'accepted'],
        ]);
        $customerId = $request->user('web')->id;
        $review = DB::transaction(function () use ($order, $experience, $data, $customerId) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless((int) $order->user_id === (int) $customerId, 404);
            $brand = $experience->reviewBrand($order);
            abort_unless($brand !== null, 422, 'A confirmed production purchase attributed to this brand is required.');
            $review = CustomerReview::query()->where('order_id', $order->id)->lockForUpdate()->first();
            abort_if($review && (int) $review->user_id !== (int) $order->user_id, 409, 'This order review needs support verification.');
            return CustomerReview::query()->updateOrCreate(['order_id' => $order->id], [
                'user_id' => $order->user_id, 'brand' => $brand, 'locale' => $data['locale'],
                'display_name' => trim($data['display_name']), 'rating' => $data['rating'], 'body' => trim($data['body']),
                'status' => 'pending', 'publication_consented_at' => now(), 'withdrawn_at' => null,
                'published_at' => null, 'moderated_at' => null, 'moderated_by_admin_id' => null, 'moderation_note' => null,
            ]);
        });

        return response()->json(['message' => 'Your review is saved for moderation. It is not public yet.', 'data' => $this->data($review)]);
    }

    public function destroy(Request $request, Order $order)
    {
        $this->owned($request, $order);
        CustomerReview::query()->where('order_id', $order->id)->where('user_id', $order->user_id)->update([
            'status' => 'withdrawn', 'publication_consented_at' => null, 'withdrawn_at' => now(), 'published_at' => null,
        ]);

        return response()->json(['message' => 'Publication permission withdrawn. Your review is no longer public.']);
    }

    private function owned(Request $request, Order $order): void
    {
        abort_unless($request->user('web') && (int) $order->user_id === (int) $request->user('web')->id, 404);
    }

    private function data(CustomerReview $review): array
    {
        return $review->only(['id', 'display_name', 'rating', 'body', 'locale', 'status']) + [
            'publication_consent' => $review->publication_consented_at !== null && $review->withdrawn_at === null,
        ];
    }
}
