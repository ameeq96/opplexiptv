<?php

namespace App\Services;

use App\Models\MarketingDelivery;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;

class EventPromotionService
{
    public const DISCOUNT_PERCENT = 10;
    public const LEAD_DAYS = 7;
    public const SESSION_KEY = 'event_promotion';

    public function dueCampaigns(?CarbonInterface $moment = null): array
    {
        if (!config('services.marketing.event_promotions.enabled', true)) {
            return [];
        }

        $now = $this->moment($moment);
        $campaigns = [];

        foreach ([$now->year - 1, $now->year, $now->year + 1] as $year) {
            foreach ($this->campaignsForYear($year) as $campaign) {
                if ($now->betweenIncluded($campaign['starts_at'], $campaign['ends_at'])) {
                    $campaigns[] = $campaign;
                }
            }
        }

        return $campaigns;
    }

    public function activeCampaign(string $campaignId, ?CarbonInterface $moment = null): ?array
    {
        foreach ($this->dueCampaigns($moment) as $campaign) {
            if (hash_equals($campaign['id'], $campaignId)) {
                return $campaign;
            }
        }

        return null;
    }

    public function campaignForDelivery(MarketingDelivery $delivery): ?array
    {
        if ($delivery->workflow !== 'promotion' || $delivery->channel !== 'email') {
            return null;
        }

        $campaignId = trim((string) ($delivery->payload['campaign_id'] ?? ''));

        return $campaignId === '' ? null : $this->activeCampaign($campaignId);
    }

    public function emailPayload(array $campaign, User $user): array
    {
        $opening = trim((string) $user->name) !== ''
            ? 'Hi ' . trim((string) $user->name) . ', our'
            : 'Our';

        return [
            'campaign_id' => $campaign['id'],
            'event_name' => $campaign['name'],
            'discount_percent' => self::DISCOUNT_PERCENT,
            'starts_at' => $campaign['starts_at']->toIso8601String(),
            'ends_at' => $campaign['ends_at']->toIso8601String(),
            'subject' => $campaign['name'] . ': 10% off your Opplex IPTV order',
            'body' => $opening . ' ' . $campaign['name']
                . ' offer gives you 10% off an Opplex IPTV order. Activate your personal offer and complete checkout by '
                . $campaign['ends_at']->format('F j, Y') . '.',
            'cta_text' => 'Activate 10% discount',
        ];
    }

    public function whatsappClickMessage(?CarbonInterface $moment = null): ?string
    {
        $campaign = $this->dueCampaigns($moment)[0] ?? null;
        if (!$campaign) {
            return null;
        }

        return $this->whatsappClickMessageForCampaign($campaign);
    }

    public function whatsappClickCampaigns(?CarbonInterface $moment = null): array
    {
        if (!config('services.marketing.event_promotions.enabled', true)) {
            return [];
        }

        $now = $this->moment($moment);
        $campaigns = [];

        foreach ([$now->year - 1, $now->year, $now->year + 1] as $year) {
            foreach ($this->campaignsForYear($year) as $campaign) {
                $campaigns[] = [
                    'starts_at' => $campaign['starts_at']->toIso8601String(),
                    'ends_at' => $campaign['ends_at']->toIso8601String(),
                    'message' => $this->whatsappClickMessageForCampaign($campaign),
                ];
            }
        }

        return $campaigns;
    }

    public function activationUrl(MarketingDelivery $delivery): string
    {
        $campaign = $this->campaignForDelivery($delivery);
        if (!$campaign) {
            throw new RuntimeException('Promotional campaign is no longer active.');
        }

        return URL::temporarySignedRoute(
            'event-promotions.activate',
            $campaign['ends_at']->utc(),
            [
                'delivery' => $delivery->id,
                'locale' => $delivery->locale,
            ]
        );
    }

    public function activate(MarketingDelivery $delivery): ?array
    {
        $campaign = $this->campaignForDelivery($delivery);
        if (!$campaign
            || !$delivery->sent_at
            || !$delivery->user
            || !$this->isEligible($delivery->user)) {
            return null;
        }

        session()->put(self::SESSION_KEY, [
            'delivery_id' => $delivery->id,
            'activated_at' => now()->toIso8601String(),
        ]);

        return $campaign;
    }

    public function activeForSession(): ?array
    {
        $deliveryId = (int) session(self::SESSION_KEY . '.delivery_id', 0);
        if ($deliveryId < 1) {
            return null;
        }

        $delivery = MarketingDelivery::query()->with('user')->find($deliveryId);
        $campaign = $delivery ? $this->campaignForDelivery($delivery) : null;

        if (!$delivery
            || !$campaign
            || !$delivery->sent_at
            || !$delivery->user
            || !$this->isEligible($delivery->user)) {
            $this->forget();

            return null;
        }

        return $campaign + [
            'delivery_id' => $delivery->id,
            'recipient_user_id' => $delivery->user_id,
            'recipient_email_normalized' => $delivery->user->email_normalized,
        ];
    }

    public function quote(float $subtotal, array $campaign): array
    {
        $subtotal = round(max(0, $subtotal), 2);
        $discount = round($subtotal * (self::DISCOUNT_PERCENT / 100), 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round(max(0, $subtotal - $discount), 2),
        ];
    }

    public function isEligible(User $user): bool
    {
        return (bool) $user->email_normalized
            && $user->hasMarketingConsent('email')
            && ($user->orders()->exists() || $user->digitalOrders()->exists());
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function campaignsForYear(int $year): array
    {
        $timezone = $this->timezone();
        $thanksgiving = $this->nthWeekdayOfMonth($year, 11, CarbonInterface::THURSDAY, 4, $timezone);

        $events = [
            'new-year' => ['New Year', CarbonImmutable::create($year, 1, 1, 0, 0, 0, $timezone)],
            'valentines-day' => ["Valentine's Day", CarbonImmutable::create($year, 2, 14, 0, 0, 0, $timezone)],
            'mothers-day' => ["Mother's Day", $this->nthWeekdayOfMonth($year, 5, CarbonInterface::SUNDAY, 2, $timezone)],
            'fathers-day' => ["Father's Day", $this->nthWeekdayOfMonth($year, 6, CarbonInterface::SUNDAY, 3, $timezone)],
            'halloween' => ['Halloween', CarbonImmutable::create($year, 10, 31, 0, 0, 0, $timezone)],
            'black-friday' => ['Black Friday', $thanksgiving->addDay()],
            'cyber-monday' => ['Cyber Monday', $thanksgiving->addDays(4)],
            'christmas' => ['Christmas', CarbonImmutable::create($year, 12, 25, 0, 0, 0, $timezone)],
        ];

        $campaigns = [];
        foreach ($events as $key => [$name, $eventDate]) {
            $campaigns[] = [
                'id' => $key . '-' . $eventDate->format('Y'),
                'key' => $key,
                'name' => $name,
                'event_at' => $eventDate,
                'starts_at' => $eventDate->subDays(self::LEAD_DAYS)->startOfDay(),
                'ends_at' => $eventDate->endOfDay(),
                'discount_percent' => self::DISCOUNT_PERCENT,
            ];
        }

        return $campaigns;
    }

    private function nthWeekdayOfMonth(
        int $year,
        int $month,
        int $weekday,
        int $occurrence,
        string $timezone
    ): CarbonImmutable {
        $first = CarbonImmutable::create($year, $month, 1, 0, 0, 0, $timezone);
        $offset = ($weekday - $first->dayOfWeek + 7) % 7;

        return $first->addDays($offset + (($occurrence - 1) * 7));
    }

    private function moment(?CarbonInterface $moment): CarbonImmutable
    {
        $timezone = $this->timezone();

        return $moment
            ? CarbonImmutable::instance($moment)->setTimezone($timezone)
            : CarbonImmutable::now($timezone);
    }

    private function whatsappClickMessageForCampaign(array $campaign): string
    {
        return Lang::get('marketing.event_promotion.whatsapp_click_message', [
            'event' => $this->localizedEventName($campaign),
            'date' => $campaign['ends_at']->format('Y-m-d'),
        ]);
    }

    private function localizedEventName(array $campaign, ?string $locale = null): string
    {
        $key = 'marketing.event_promotion.events.' . $campaign['key'];
        $translated = Lang::get($key, [], $locale);

        return $translated === $key ? $campaign['name'] : $translated;
    }

    private function timezone(): string
    {
        $timezone = (string) config('services.marketing.event_promotions.timezone', 'UTC');

        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (Throwable) {
            return 'UTC';
        }
    }
}
