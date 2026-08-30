<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'pixel_id'   => env('FACEBOOK_PIXEL_ID'),
        'pixel_ids'  => array_values(array_filter(array_map('trim', explode(',', env('FACEBOOK_PIXEL_IDS', ''))))),
        'capi_token' => env('FACEBOOK_CAPI_TOKEN'),
        'test_code'  => env('FB_TEST_EVENT_CODE'),
    ],
    'app' => [
        'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
    ],
    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER'),
        'cloud_token' => env('WHATSAPP_CLOUD_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'graph_version' => env('WHATSAPP_GRAPH_VERSION'),
        'templates' => [
            'abandoned' => env('WHATSAPP_TEMPLATE_ABANDONED'),
            'onboarding' => env('WHATSAPP_TEMPLATE_ONBOARDING'),
            'renewal' => env('WHATSAPP_TEMPLATE_RENEWAL'),
            'referral' => env('WHATSAPP_TEMPLATE_REFERRAL'),
        ],
    ],
    'google' => [
        'tag_manager_id' => env('GTM_CONTAINER_ID'),
        'analytics_id'   => env('GA_MEASUREMENT_ID', 'G-L98JG9ZT7H'),
        'measurement_protocol_secret' => env('GA_MEASUREMENT_PROTOCOL_SECRET'),
    ],
    'clarity' => [
        'project_id' => env('CLARITY_PROJECT_ID', 'sq6nn3dn69'),
    ],
    'payment_webhook' => [
        'secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'tolerance_seconds' => (int) env('PAYMENT_WEBHOOK_TOLERANCE_SECONDS', 300),
        'providers' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PAYMENT_WEBHOOK_PROVIDERS', ''))
        ))),
    ],
    'marketing' => [
        'consent_version' => env('MARKETING_CONSENT_VERSION', '2026-08'),
        'tracking_consent_version' => env('TRACKING_CONSENT_VERSION', '2026-08-29.1'),
        'abandoned_after_minutes' => (int) env('MARKETING_ABANDONED_AFTER_MINUTES', 60),
        'draft_retention_days' => (int) env('MARKETING_DRAFT_RETENTION_DAYS', 30),
        'delivery_retention_days' => (int) env('MARKETING_DELIVERY_RETENTION_DAYS', 90),
        'referral_after_days' => (int) env('MARKETING_REFERRAL_AFTER_DAYS', 3),
        'referral_reward_type' => env('REFERRAL_REWARD_TYPE'),
        'referral_reward_value' => env('REFERRAL_REWARD_VALUE'),
        'referral_reward_currency' => env('REFERRAL_REWARD_CURRENCY', 'USD'),
    ],
    'tmdb' => [
        'base_url' => env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'),
        'api_key'  => env('TMDB_API_KEY', ''),
    ],
    'discount' => [
        'phone' => env('DISCOUNT_WA_PHONE', '16393903194'),
    ],
    'amazon_affiliate_tag' => env('AMAZON_AFFILIATE_TAG', 'opplexstore-20'),

];
