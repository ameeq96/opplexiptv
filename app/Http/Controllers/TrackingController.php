<?php

namespace App\Http\Controllers;

use App\Jobs\SendFacebookCapiEvent;
use App\Models\Order;
use App\Models\TrialClick;
use App\Services\Clients\CustomerIdentityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Nakanakaii\Countries\Countries;

class TrackingController extends Controller
{
    public function whatsappLeadToken(Request $request)
    {
        $supportedCountries = array_fill_keys(array_column(Countries::all(), 'code'), true);
        $countryCode = null;

        foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Vercel-IP-Country'] as $countryHeader) {
            $candidate = strtoupper(trim((string) $request->header($countryHeader, '')));
            if (preg_match('/^[A-Z]{2}$/', $candidate) === 1 && isset($supportedCountries[$candidate])) {
                $countryCode = $candidate;
                break;
            }
        }

        return response()
            ->json([
                'csrf_token' => csrf_token(),
                'country_code' => $countryCode,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function storeWhatsAppLead(Request $request, CustomerIdentityService $identity)
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'destination' => [
                'required',
                'string',
                'max:512',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$this->isWhatsAppDestination((string) $value)) {
                        $fail('The selected WhatsApp destination is invalid.');
                    }
                },
            ],
            'page' => ['nullable', 'url', 'max:512'],
            'contact_name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s().-]+$/'],
            'contact_consent' => ['required', 'accepted'],
            'locale' => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z]{2})?$/'],
            'fbp' => ['nullable', 'string', 'max:128'],
            'fbc' => ['nullable', 'string', 'max:256'],
            'intent' => ['nullable', 'string', 'max:32'],
            'placement' => ['nullable', 'string', 'max:128'],
            'package' => ['nullable', 'string', 'max:191'],
            'vendor' => ['nullable', 'string', 'max:32'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'is_trial' => ['nullable', 'boolean'],
        ]);

        $phone = preg_replace('/\D+/', '', $data['phone']) ?? '';
        if (preg_match('/^[1-9]\d{7,14}$/', $phone) !== 1) {
            throw ValidationException::withMessages([
                'phone' => 'Enter an international WhatsApp number with its country code.',
            ]);
        }

        $user = $identity->resolve(null, $phone);

        $normalize = static function ($value, int $maxLength): ?string {
            if (!is_string($value) && !is_numeric($value)) {
                return null;
            }

            $value = trim((string) $value);
            return $value === '' ? null : mb_substr($value, 0, $maxLength);
        };

        $eventId = $data['event_id'];
        $destination = $data['destination'];
        $page = $normalize($data['page'] ?? $request->headers->get('referer'), 512);
        $intent = $normalize($data['intent'] ?? null, 32)
            ?? (($data['is_trial'] ?? false) ? 'trial' : 'contact');
        $placement = $normalize($data['placement'] ?? null, 128);
        $packageName = $normalize($data['package'] ?? null, 191);
        $vendor = $normalize($data['vendor'] ?? null, 32);
        $value = isset($data['value']) ? round((float) $data['value'], 2) : null;
        $currency = isset($data['currency']) ? strtoupper($data['currency']) : null;
        $locale = strtolower(str_replace('_', '-', $data['locale'] ?? app()->getLocale()));
        $referrer = $normalize($request->headers->get('referer'), 2048);
        $clientIp = (string) ($request->ip() ?? '');
        $ipHash = $clientIp === ''
            ? null
            : hash_hmac('sha256', $clientIp, (string) config('app.key'));
        $consentedAt = now();
        $marketingConsent = $this->hasMarketingConsent($request);
        if (!$marketingConsent) {
            $page = $this->urlWithoutQueryOrFragment($page);
            $referrer = $this->urlWithoutQueryOrFragment($referrer);
        }
        $fbp = $marketingConsent
            ? $normalize(($data['fbp'] ?? null) ?: $request->cookie('_fbp'), 128)
            : null;
        $fbc = $marketingConsent
            ? $normalize(($data['fbc'] ?? null) ?: $request->cookie('_fbc'), 256)
            : null;

        $utm = [
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_term' => null,
            'utm_content' => null,
        ];
        if ($marketingConsent) {
            foreach ($utm as $key => $unused) {
                $utm[$key] = $normalize($request->session()->get('fb.'.$key), 128);
            }

            if ($page && ($query = parse_url($page, PHP_URL_QUERY))) {
                parse_str($query, $queryValues);
                foreach ($utm as $key => $currentValue) {
                    $pageValue = $normalize($queryValues[$key] ?? null, 128);
                    if ($pageValue !== null) {
                        $utm[$key] = $pageValue;
                    }
                }
            }
        }

        $click = TrialClick::firstOrCreate(
            ['phone_normalized' => $phone],
            [
                'user_id' => $user?->id,
                'event_id' => $eventId,
                'last_event_id' => $eventId,
                'contact_name' => $normalize($data['contact_name'], 120),
                'destination' => $destination,
                'page' => $page,
                'intent' => $intent,
                'placement' => $placement,
                'package_name' => $packageName,
                'vendor' => $vendor,
                'value' => $value,
                'currency' => $currency,
                'status' => 'new',
                'whatsapp_contact_consented_at' => $consentedAt,
                'consent_version' => TrialClick::WHATSAPP_CONTACT_CONSENT_VERSION,
                'consent_source' => 'website_whatsapp_capture',
                'consent_locale' => $locale,
                'consent_ip_hash' => $ipHash,
                'click_count' => 1,
                'fbp' => $fbp,
                'fbc' => $fbc,
                'user_agent' => $marketingConsent ? $request->userAgent() : null,
                'utm_source' => $utm['utm_source'],
                'utm_medium' => $utm['utm_medium'],
                'utm_campaign' => $utm['utm_campaign'],
                'utm_term' => $utm['utm_term'],
                'utm_content' => $utm['utm_content'],
                'referrer' => $referrer,
            ]
        );

        $wasRecentlyCreated = $click->wasRecentlyCreated;
        $isNewEvent = $wasRecentlyCreated || (string) $click->last_event_id !== $eventId;

        if (!$wasRecentlyCreated) {
            $updates = [
                'contact_name' => $normalize($data['contact_name'], 120),
                'destination' => $destination,
                'page' => $page,
                'intent' => $intent,
                'placement' => $placement,
                'package_name' => $packageName,
                'vendor' => $vendor,
                'value' => $value,
                'currency' => $currency,
                'referrer' => $referrer,
                'whatsapp_contact_consented_at' => $consentedAt,
                'consent_version' => TrialClick::WHATSAPP_CONTACT_CONSENT_VERSION,
                'consent_source' => 'website_whatsapp_capture',
                'consent_locale' => $locale,
                'consent_ip_hash' => $ipHash,
            ];

            $updates['user_id'] = $user?->id;

            if ($isNewEvent) {
                $updates['last_event_id'] = $eventId;
                $updates['click_count'] = max(1, (int) $click->click_count) + 1;
            }

            if ($marketingConsent) {
                foreach ([
                    'fbp' => $fbp,
                    'fbc' => $fbc,
                    'user_agent' => $request->userAgent(),
                    'utm_source' => $utm['utm_source'],
                    'utm_medium' => $utm['utm_medium'],
                    'utm_campaign' => $utm['utm_campaign'],
                    'utm_term' => $utm['utm_term'],
                    'utm_content' => $utm['utm_content'],
                ] as $key => $trackingValue) {
                    if ($trackingValue !== null) {
                        $updates[$key] = $trackingValue;
                    }
                }
            }

            $click->forceFill($updates)->save();
        }

        if ($isNewEvent && $marketingConsent && ($data['is_trial'] ?? false) && $intent === 'trial') {
            SendFacebookCapiEvent::dispatchAfterResponse('StartTrial', [
                'event_time' => time(),
                'event_source_url' => $page ?: url('/'),
                'user_data' => [
                    'fbp' => $fbp,
                    'fbc' => $fbc,
                    'client_ip_address' => $request->ip(),
                    'client_user_agent' => $request->userAgent(),
                ],
                'custom_data' => [
                    'currency' => $currency ?: config('services.app.default_currency', 'USD'),
                    'value' => $value ?? 0,
                    'content_name' => $packageName ?: 'WhatsApp',
                    'contact_channel' => 'whatsapp',
                    'destination' => $destination,
                ],
            ], $eventId);
        }

        return response()->json([
            'ok' => true,
            'duplicate' => !$wasRecentlyCreated,
            'lead_code' => $click->lead_code,
        ], 202);
    }

    public function whatsappTrial(Request $request)
    {
        if (!$this->hasMarketingConsent($request)) {
            return response()->json(['ok' => true, 'tracked' => false], 202);
        }

        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'destination' => ['required', 'string', 'max:512'],
            'page' => ['nullable', 'url', 'max:512'],
            'fbp' => ['nullable', 'string', 'max:128'],
            'fbc' => ['nullable', 'string', 'max:256'],
            'intent' => ['nullable', 'string', 'max:32'],
            'placement' => ['nullable', 'string', 'max:128'],
            'order_id' => ['nullable', 'integer', 'min:1'],
            'package' => ['nullable', 'string', 'max:191'],
            'vendor' => ['nullable', 'string', 'max:32'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'is_trial' => ['nullable', 'boolean'],
        ]);

        $eventId = $data['event_id'];
        $dest = $data['destination'];
        $page = $data['page'] ?? null;
        $normalize = static function ($value, int $maxLength): ?string {
            if (!is_string($value) && !is_numeric($value)) {
                return null;
            }

            $value = trim((string) $value);
            return $value === '' ? null : mb_substr($value, 0, $maxLength);
        };

        $fbp = $normalize(($data['fbp'] ?? null) ?: $request->cookie('_fbp'), 128);
        $fbc = $normalize(($data['fbc'] ?? null) ?: $request->cookie('_fbc'), 256);
        $intent = $normalize($data['intent'] ?? null, 32)
            ?? (($data['is_trial'] ?? false) ? 'trial' : 'contact');
        $placement = $normalize($data['placement'] ?? null, 128);
        $isTrial = ($data['is_trial'] ?? false) && $intent === 'trial';
        $orderId = isset($data['order_id']) ? (int) $data['order_id'] : null;
        $whatsappPaymentOrders = (array) $request->session()->get('whatsapp_payment_orders', []);
        $paymentContextCreatedAt = $orderId === null
            ? null
            : ($whatsappPaymentOrders[(string) $orderId] ?? null);
        $userId = null;

        if ($intent === 'payment'
            && $placement === 'thank_you_payment'
            && is_numeric($paymentContextCreatedAt)
            && (int) $paymentContextCreatedAt >= now()->subMinutes(30)->timestamp
        ) {
            $userId = Order::query()->whereKey($orderId)->value('user_id');
        }

        if ($userId === null) {
            return response()->json([
                'ok' => true,
                'tracked' => false,
                'requires_contact' => true,
            ], 202);
        }

        $utm = [
            'utm_source' => $normalize($request->session()->get('fb.utm_source'), 128),
            'utm_medium' => $normalize($request->session()->get('fb.utm_medium'), 128),
            'utm_campaign' => $normalize($request->session()->get('fb.utm_campaign'), 128),
            'utm_term' => $normalize($request->session()->get('fb.utm_term'), 128),
            'utm_content' => $normalize($request->session()->get('fb.utm_content'), 128),
        ];
        if ($page) {
            $qs = parse_url($page, PHP_URL_QUERY);
            if ($qs) {
                parse_str($qs, $out);
                foreach ($utm as $k => $v) {
                    $pageValue = $normalize($out[$k] ?? null, 128);
                    if ($pageValue !== null) $utm[$k] = $pageValue;
                }
            }
        }

        $click = TrialClick::firstOrCreate(
            ['event_id' => $eventId],
            [
                'user_id'      => $userId,
                'destination'  => $dest,
                'page'         => $page,
                'intent'       => $intent,
                'placement'    => $placement,
                'package_name' => $normalize($data['package'] ?? null, 191),
                'vendor'       => $normalize($data['vendor'] ?? null, 32),
                'value'        => isset($data['value']) ? round((float) $data['value'], 2) : null,
                'currency'     => isset($data['currency']) ? strtoupper($data['currency']) : null,
                'fbp'          => $fbp,
                'fbc'          => $fbc,
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'utm_source'   => $utm['utm_source'],
                'utm_medium'   => $utm['utm_medium'],
                'utm_campaign' => $utm['utm_campaign'],
                'utm_term'     => $utm['utm_term'],
                'utm_content'  => $utm['utm_content'],
                'referrer'     => $request->headers->get('referer'),
            ]
        );

        if ($userId !== null && $click->user_id === null) {
            $click->forceFill(['user_id' => $userId])->save();
        }

        if ($orderId !== null && (int) $click->user_id === (int) $userId && $userId !== null) {
            unset($whatsappPaymentOrders[(string) $orderId]);
            $request->session()->put('whatsapp_payment_orders', $whatsappPaymentOrders);
        }

        $payload = [
            'event_time'       => time(),
            'event_source_url' => $page ?: url('/'),
            'user_data' => [
                'fbp' => $fbp ?: null,
                'fbc' => $fbc ?: null,
                'client_ip_address' => $request->ip(),
                'client_user_agent' => $request->userAgent(),
            ],
            'custom_data' => [
                'currency' => config('services.app.default_currency', 'USD'),
                'value'    => 0,
                'content_name'    => 'WhatsApp',
                'contact_channel' => 'whatsapp',
                'destination'     => $dest,
            ],
        ];

        if ($click->wasRecentlyCreated && $isTrial) {
            SendFacebookCapiEvent::dispatchAfterResponse('StartTrial', $payload, $eventId);
        }

        return response()->json([
            'ok' => true,
            'duplicate' => !$click->wasRecentlyCreated,
            'lead_code' => $click->lead_code,
        ], 202);
    }

    private function hasMarketingConsent(Request $request): bool
    {
        foreach (explode(';', (string) $request->headers->get('cookie')) as $cookie) {
            [$key, $value] = array_pad(explode('=', trim($cookie), 2), 2, null);
            if ($key === 'opplex_consent' && $value !== null) {
                $preference = json_decode(urldecode($value), true);

                return is_array($preference)
                    && ($preference['marketing'] ?? false) === true
                    && ($preference['version'] ?? null) === config('services.marketing.tracking_consent_version');
            }
        }

        return false;
    }

    private function isWhatsAppDestination(string $destination): bool
    {
        $parts = parse_url(trim($destination));
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = preg_replace('/^www\./', '', strtolower((string) ($parts['host'] ?? ''))) ?? '';

        if ($scheme === 'whatsapp') {
            return $host === 'send';
        }

        return in_array($scheme, ['http', 'https'], true)
            && ($host === 'wa.me' || $host === 'whatsapp.com' || str_ends_with($host, '.whatsapp.com'));
    }

    private function urlWithoutQueryOrFragment(?string $url): ?string
    {
        if (!$url || !is_array($parts = parse_url($url))) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }

        $port = isset($parts['port']) ? ':'.(int) $parts['port'] : '';
        $path = (string) ($parts['path'] ?? '/');

        return $scheme.'://'.$host.$port.($path !== '' ? $path : '/');
    }
}
