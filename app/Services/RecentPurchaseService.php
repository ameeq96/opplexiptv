<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecentPurchaseService
{
    public function items(): array
    {
        try {
            return Cache::remember('social-proof:purchasers:v4', now()->addMinutes(10), function () {
                return Order::query()
                    ->select([
                        'id',
                        'user_id',
                        'package',
                        'custom_package',
                        'paid_at',
                    ])
                    ->with('user:id,name')
                    ->where(function ($query) {
                        $query->where('payment_status', 'paid')
                            ->orWhereIn('status', ['active', 'expired']);
                    })
                    ->whereNotNull('package')
                    ->where('package', '<>', '')
                    ->whereHas('user', function ($query) {
                        $query->whereNotNull('name')->where('name', '<>', '');
                    })
                    ->orderByDesc('id')
                    ->limit(40)
                    ->get()
                    ->map(function (Order $order) {
                        $package = $order->package === 'other' && $order->custom_package
                            ? $order->custom_package
                            : $order->package;
                        $package = Str::limit(Str::squish(strip_tags((string) $package)), 80, '…');
                        $name = $this->maskName($order->user?->name);

                        return $name !== null && $package !== ''
                            ? ['name' => $name, 'package' => $package]
                            : null;
                    })
                    ->filter()
                    ->values()
                    ->all();
            });
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function maskName(?string $name): ?string
    {
        $parts = preg_split('/\s+/u', trim(strip_tags((string) $name)), -1, PREG_SPLIT_NO_EMPTY);
        if (! $parts) {
            return null;
        }

        $first = $parts[0];
        $masked = mb_substr($first, 0, 1).str_repeat('*', min(5, max(2, mb_strlen($first) - 1)));

        if (count($parts) > 1) {
            $masked .= ' '.mb_substr($parts[count($parts) - 1], 0, 1).'.';
        }

        return $masked;
    }
}
