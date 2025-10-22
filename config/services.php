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
    ],
    'google' => [
        'tag_manager_id' => env('GTM_CONTAINER_ID'),
        'analytics_id'   => env('GA_MEASUREMENT_ID'),
    ],
    'discount' => [
        'phone' => env('DISCOUNT_WA_PHONE', '16393903194'),
    ],
    'amazon_affiliate_tag' => env('AMAZON_AFFILIATE_TAG', 'opplexstore-20'),

];
