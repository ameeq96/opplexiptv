<?php

return [
    'supported_locales' => ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'],
    'timezone' => 'Asia/Karachi',
    'time' => '10:00',
    'per_page' => 12,
    'publisher' => ['name' => 'Opplex IPTV Subscription Services', 'type' => 'Organization'],
    'google' => ['credentials_file' => storage_path('app/private/blog-google-service-account.json')],
    'sites' => [
        'main' => ['name' => 'Opplex IPTV', 'domain' => 'opplexiptv.com', 'google_property' => 'sc-domain:opplexiptv.com', 'indexnow_key_path' => '/978a20800d5de9c0b1a2ff9b51aed86c.txt'],
        'trex' => ['name' => 'Trex IPTV 8K', 'domain' => 'trexiptv8k.store', 'google_property' => 'sc-domain:trexiptv8k.store', 'indexnow_key_path' => '/indexnow-key.txt'],
        'opplex' => ['name' => 'Opplex IPTV Store', 'domain' => 'opplexiptv.store', 'google_property' => 'sc-domain:opplexiptv.store', 'indexnow_key_path' => '/indexnow-key.txt'],
        'geo' => ['name' => 'Geo IPTV', 'domain' => 'geoiptv.store', 'google_property' => 'sc-domain:geoiptv.store', 'indexnow_key_path' => '/indexnow-key.txt'],
        '5g' => ['name' => '5G Live IPTV', 'domain' => '5gliveiptv.store', 'google_property' => 'sc-domain:5gliveiptv.store', 'indexnow_key_path' => '/indexnow-key.txt'],
        'iptvsubscription' => ['name' => 'IPTV Subscription', 'domain' => 'iptvsubscription.shop', 'google_property' => 'https://iptvsubscription.shop/', 'indexnow_key_path' => '/indexnow-key.txt'],
        'getiptv' => ['name' => 'Get IPTV', 'domain' => 'getiptv.store', 'google_property' => 'https://getiptv.store/', 'indexnow_key_path' => '/indexnow-key.txt'],
    ],
];
