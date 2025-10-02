<?php

return [
    'pixel_id'          => env('META_PIXEL_ID', '1467807554407581'), // fallback to current
    'access_token'      => env('FB_ACCESS_TOKEN', ''),
    'test_event_code'   => env('FB_TEST_EVENT_CODE', ''),
    'default_currency'  => env('FB_DEFAULT_CURRENCY', 'PKR'),
    'tracking_enabled'  => env('FB_TRACKING_ENABLED', true),
    'domain_verification' => env('FACEBOOK_DOMAIN_VERIFICATION', null),
];