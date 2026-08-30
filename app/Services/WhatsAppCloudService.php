<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppCloudService
{
    public function sendTemplate(string $phone, string $workflow, string $locale, array $parameters): array
    {
        $token = config('services.whatsapp.cloud_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $graphVersion = config('services.whatsapp.graph_version');
        $template = config('services.whatsapp.templates.' . $workflow);

        if (!$token || !$phoneNumberId || !$graphVersion || !$template) {
            return ['skipped' => true, 'reason' => 'missing_whatsapp_template_config'];
        }

        $languageCodes = [
            'en' => 'en_US', 'es' => 'es_ES', 'fr' => 'fr_FR', 'it' => 'it_IT',
            'nl' => 'nl_NL', 'pt' => 'pt_PT', 'ru' => 'ru_RU', 'ur' => 'ur_PK',
            'ar' => 'ar', 'hi' => 'hi_IN',
        ];

        $response = Http::withToken($token)
            ->asJson()
            ->connectTimeout(3)
            ->timeout(10)
            ->post("https://graph.facebook.com/{$graphVersion}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => preg_replace('/\D+/', '', $phone),
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $languageCodes[$locale] ?? 'en_US'],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => array_map(
                            fn ($value) => ['type' => 'text', 'text' => (string) $value],
                            $parameters
                        ),
                    ]],
                ],
            ]);

        $response->throw();

        return [
            'skipped' => false,
            'message_id' => data_get($response->json(), 'messages.0.id'),
        ];
    }
}
