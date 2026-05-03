<?php

return [
    // TRTC SDK credentials (Tencent RTC — 1:1 video interview)
    'sdk_app_id'   => env('TRTC_SDK_APP_ID'),
    'secret_key'   => env('TRTC_SECRET_KEY'),
    'expire'       => 86400, // User sig expire in seconds (24h)

    // Tencent CSS (Live streaming for seminars)
    'push_domain'       => env('TRTC_PUSH_DOMAIN'),
    'play_domain'       => env('TRTC_PLAY_DOMAIN'),
    'push_key'          => env('TRTC_PUSH_KEY'),
    'play_key'          => env('TRTC_PLAY_KEY'),
    'app_name'          => env('TRTC_APP_NAME', 'live'),
    'callback_hash_key' => env('TRTC_CALLBACK_HASH_KEY', ''),
];
