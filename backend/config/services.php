<?php

return [
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/social/google/callback',
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/social/facebook/callback',
    ],
    'linkedin-openid' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/social/linkedin/callback',
    ],
    // WeChat (Weixin) OAuth requires China ICP registration and mainland users.
    // Uses SocialiteProviders/WeChat which registers the 'weixin' driver.
    'weixin' => [
        'client_id'     => env('WECHAT_APP_ID'),
        'client_secret' => env('WECHAT_APP_SECRET'),
        'redirect'      => env('APP_URL').'/api/auth/social/wechat/callback',
    ],
];
