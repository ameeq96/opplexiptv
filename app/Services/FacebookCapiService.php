<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookCapiService
{
    public function send(string $eventName, array $payload, ?string $eventId = null): array
    {
        $pixelId     = config('services.facebook.pixel_id');
        $accessToken = config('services.facebook.capi_token');
        $testCode    = config('services.facebook.test_code');

        if (!$pixelId || !$accessToken) {
            return ['skipped' => true, 'reason' => 'missing_config'];
        }

        $data = [[
            'event_name'       => $eventName,
            'event_time'       => $payload['event_time'] ?? time(),
            'event_id'         => $eventId,
            'action_source'    => $payload['action_source'] ?? 'website',
            'event_source_url' => $payload['event_source_url'] ?? url('/'),
            'user_data'        => array_filter([
                'fbp' => $payload['user_data']['fbp'] ?? null,
                'fbc' => $payload['user_data']['fbc'] ?? null,
                'client_ip_address' => $payload['user_data']['client_ip_address'] ?? null,
                'client_user_agent' => $payload['user_data']['client_user_agent'] ?? null,
                // email/phone optional if available later
            ]),
            'custom_data'      => $payload['custom_data'] ?? [],
        ]];

        $body = ['data' => $data];
        if ($testCode) $body['test_event_code'] = $testCode;

        $resp = Http::asJson()->post(
            "https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$accessToken}",
            $body
        );

        return ['status' => $resp->status(), 'body' => $resp->json()];
    }
}
