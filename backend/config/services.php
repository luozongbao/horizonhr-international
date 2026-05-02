<?php

return [
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/oauth/google/callback',
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/oauth/facebook/callback',
    ],
    'linkedin-openid' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/oauth/linkedin/callback',
    ],
    'weixin' => [
        'client_id'     => env('WECHAT_APP_ID'),
        'client_secret' => env('WECHAT_APP_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/oauth/wechat/callback',
    ],
];
