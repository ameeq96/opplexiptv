<?php

namespace App\Services\Clients;

use App\Models\CustomerActivity;
use App\Models\Order;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LandingInteractionService
{
    public const BRANDS = [
        'trex' => ['name' => 'Trex IPTV 8K', 'domain' => 'trexiptv8k.store', 'port' => 8101],
        'opplex' => ['name' => 'Opplex IPTV', 'domain' => 'opplexiptv.store', 'port' => 8102],
        'geo' => ['name' => 'Geo IPTV', 'domain' => 'geoiptv.store', 'port' => 8103],
        '5g' => ['name' => '5G Live IPTV', 'domain' => '5gliveiptv.store', 'port' => 8104],
    ];
    public const CONTACT_CONSENT_VERSION = 'landing-contact-2026-10-16.1';
    public const PAYMENT_ATTRIBUTION_ENABLED = true;

    public function __construct(private CustomerActivityService $activities) {}

    public function assertOrigin(string $brand, string $origin): void
    {
        $definition = self::BRANDS[$brand];
        $allowed = ['https://'.$definition['domain'], 'https://www.'.$definition['domain']];
        if (app()->environment(['local', 'testing'])) {
            $allowed[] = 'http://127.0.0.1:'.$definition['port'];
            $allowed[] = 'http://localhost:'.$definition['port'];
        }
        abort_unless(in_array(strtolower($origin), $allowed, true), 403, 'This landing origin is not allowed.');
    }

    public function recordEvent(array $data): void
    {
        $attributes = [
            'source' => 'landing_'.$data['brand'],
            'event_id' => strtolower($data['event_id']),
            'event_type' => 'landing.'.$data['event'],
            'entity_type' => 'landing_session',
            'entity_id' => strtolower($data['session_id']),
            'user_id' => null,
            'details' => [
                'brand' => $data['brand'],
                'analytics_consent' => true,
                'package_id' => isset($data['package_id']) ? (int) $data['package_id'] : null,
            ],
        ];
        $this->activities->record($attributes);
    }

    public function recordLead(array $data): void
    {
        $analyticsConsent = (bool) ($data['analytics_consent'] ?? false);
        $attributes = [
            'source' => 'landing_'.$data['brand'],
            'event_id' => strtolower($data['event_id']),
            'event_type' => 'landing.contact_requested',
            'entity_type' => 'trial_click',
            'user_id' => null,
            'contact_name' => trim($data['name']),
            'contact_email' => User::normalizeEmail($data['email']),
            'contact_phone' => $data['phone'],
            'message' => isset($data['message']) ? trim($data['message']) : null,
            'details' => [
                'brand' => $data['brand'],
                'channel' => $data['channel'],
                'contact_verified' => false,
                'contact_consent' => true,
                'consent_version' => self::CONTACT_CONSENT_VERSION,
                'analytics_consent' => $analyticsConsent,
                'session_id' => $analyticsConsent && !empty($data['session_id']) ? strtolower($data['session_id']) : null,
            ],
        ];

        DB::transaction(function () use ($attributes, $data) {
            $activity = $this->activities->record($attributes);
            if (!$activity->wasRecentlyCreated) {
                return;
            }

            $contact = [
                'contact_name' => $attributes['contact_name'],
                'contact_email' => $attributes['contact_email'],
                'message' => $attributes['message'],
                'page' => 'https://'.self::BRANDS[$data['brand']]['domain'].'/',
                'placement' => 'landing_'.$data['brand'],
                'destination' => $data['channel'] === 'tawk' ? 'https://tawk.to' : $this->whatsappDestination(),
            ];
            if ($data['channel'] === 'whatsapp') {
                $contact += [
                    'whatsapp_contact_consented_at' => now(),
                    'consent_version' => self::CONTACT_CONSENT_VERSION,
                    'consent_source' => 'landing_'.$data['brand'].'_whatsapp',
                    'consent_locale' => 'en',
                ];
            }
            $lead = TrialClick::query()->firstOrCreate(['phone_normalized' => $data['phone']], $contact + [
                'event_id' => strtolower($data['event_id']),
                'last_event_id' => strtolower($data['event_id']),
                'intent' => 'contact',
                'status' => 'new',
                'user_id' => null,
                'click_count' => 1,
            ]);
            if (!$lead->wasRecentlyCreated) {
                $lead = TrialClick::query()->lockForUpdate()->findOrFail($lead->id);
                // Unverified form input must never replace a verified customer's lead identity.
                $changes = !$lead->user_id && !$lead->trial_owner_verified_at ? $contact : [];
                $lead->fill($changes + [
                    'last_event_id' => strtolower($data['event_id']),
                    'click_count' => (int) $lead->click_count + 1,
                ])->save();
            }
            $activity->update(['entity_id' => (string) $lead->id]);
        });
    }

    public static function excludeAnonymousActivities(Builder $query): void
    {
        $query->where(function ($visible) {
            $visible->whereNotIn('source', array_map(fn ($brand) => 'landing_'.$brand, array_keys(self::BRANDS)))
                ->orWhereNotIn('event_type', ['landing.visit', 'landing.checkout_clicked'])
                ->orWhereNull('entity_type')->orWhere('entity_type', '!=', 'landing_session');
        });
    }

    public function filterLeads(Builder $query, ?string $brand, ?string $from = null, ?string $to = null): void
    {
        if (!isset(self::BRANDS[$brand ?? ''])) {
            return;
        }
        $query->whereIn('trial_clicks.id', CustomerActivity::query()->select('entity_id')
            ->where('source', 'landing_'.$brand)->where('event_type', 'landing.contact_requested')
            ->where('entity_type', 'trial_click')->whereNotNull('entity_id')
            ->when($from, fn ($activity) => $activity->where('occurred_at', '>=', $from.' 00:00:00'))
            ->when($to, fn ($activity) => $activity->where('occurred_at', '<=', $to.' 23:59:59')));
    }

    public function funnel(?string $brand, string $from, string $to): array
    {
        $brands = isset(self::BRANDS[$brand ?? '']) ? [$brand => self::BRANDS[$brand]] : self::BRANDS;
        $range = CustomerActivity::query()
            ->whereIn('source', array_map(fn ($key) => 'landing_'.$key, array_keys($brands)))
            ->where('occurred_at', '>=', $from.' 00:00:00')->where('occurred_at', '<=', $to.' 23:59:59');
        $counts = (clone $range)
            ->whereIn('event_type', ['landing.visit', 'landing.contact_requested', 'landing.checkout_clicked'])
            ->selectRaw("source, event_type, COUNT(DISTINCT CASE WHEN event_type = 'landing.contact_requested' THEN event_id ELSE entity_id END) AS total")
            ->groupBy('source', 'event_type')->get();
        $paidCounts = (clone $range)->where('event_type', 'landing.payment_paid')->where('entity_type', 'order')
            ->whereIn('order_id', Order::query()->select('id')->where('payment_provider', 'safepay')
                ->where('payment_status', 'paid')->whereNotNull('paid_at'))
            ->selectRaw('source, COUNT(DISTINCT order_id) AS total')->groupBy('source')->get();
        $rows = [];
        $totals = ['visits' => 0, 'contacts' => 0, 'checkout_clicks' => 0, 'paid_orders' => 0];
        foreach ($brands as $key => $definition) {
            $row = ['brand' => $key, 'name' => $definition['name']] + $totals;
            foreach (['visit' => 'visits', 'contact_requested' => 'contacts', 'checkout_clicked' => 'checkout_clicks'] as $event => $metric) {
                $row[$metric] = (int) ($counts->first(fn ($count) => $count->source === 'landing_'.$key && $count->event_type === 'landing.'.$event)?->total ?? 0);
                $totals[$metric] += $row[$metric];
            }
            $row['paid_orders'] = (int) ($paidCounts->firstWhere('source', 'landing_'.$key)?->total ?? 0);
            $totals['paid_orders'] += $row['paid_orders'];
            $rows[] = $row;
        }

        return ['from' => $from, 'to' => $to, 'brands' => $rows, 'totals' => $totals, 'payment_attribution' => self::PAYMENT_ATTRIBUTION_ENABLED];
    }

    private function whatsappDestination(): ?string
    {
        $number = User::normalizePhone(config('services.whatsapp.number'));

        return $number === null ? null : 'https://wa.me/'.$number;
    }
}
