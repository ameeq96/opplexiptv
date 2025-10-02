<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FacebookCapiService
{
    public function sendEvent(string $eventName, string $eventId, array $userData = [], array $customData = []): void
    {
        if (!config('facebook.tracking_enabled')) return;

        $pixelId     = config('facebook.pixel_id');
        $accessToken = config('facebook.access_token');
        if (!$pixelId || !$accessToken) return;

        $request = request();
        $payload = [
            'data' => [[
                'event_name'        => $eventName,
                'event_time'        => time(),
                'event_source_url'  => $request->fullUrl(),
                'action_source'     => 'website',
                'event_id'          => $eventId,
                'user_data'         => array_merge($this->normalizeUserData($userData), [
                    'client_ip_address' => $request->ip(),
                    'client_user_agent' => $request->userAgent(),
                    'fbp'               => $request->cookie('_fbp'),
                    'fbc'               => $request->cookie('_fbc') ?: ($request->query('fbclid') ? 'fb.1.'.time().'.'.$request->query('fbclid') : null),
                ]),
                'custom_data'       => array_merge(['currency' => config('facebook.default_currency', 'PKR')], $customData),
            ]],
        ];
        if ($code = config('facebook.test_event_code')) $payload['test_event_code'] = $code;

        $endpoint = "https://graph.facebook.com/v18.0/{$pixelId}/events";
        try {
            Http::asJson()->timeout(8)->post($endpoint, $payload)->throw();
        } catch (\Throwable $e) {
            // optionally: \Log::warning('Facebook CAPI error: '.$e->getMessage());
        }
    }

    public function generateEventId(): string { return (string) Str::uuid(); }

    private function normalizeUserData(array $userData): array
    {
        $out = [];
        if (!empty($userData['email'])) $out['em'] = [ hash('sha256', strtolower(trim($userData['email']))) ];
        if (!empty($userData['phone'])) $out['ph'] = [ hash('sha256', preg_replace('/\D+/', '', $userData['phone'])) ];
        if (!empty($userData['first_name'])) $out['fn'] = [ hash('sha256', strtolower(trim($userData['first_name']))) ];
        if (!empty($userData['last_name']))  $out['ln'] = [ hash('sha256', strtolower(trim($userData['last_name']))) ];
        if (!empty($userData['city']))       $out['ct'] = [ hash('sha256', strtolower(trim($userData['city']))) ];
        if (!empty($userData['country']))    $out['country'] = [ hash('sha256', strtolower(trim($userData['country']))) ];
        if (!empty($userData['zip']))        $out['zp'] = [ hash('sha256', strtolower(trim($userData['zip']))) ];
        return $out;
    }
}
