<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Weixin\WeixinExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the WeChat (Weixin) Socialite driver.
        // SocialiteProviders/Manager fires SocialiteWasCalled during boot;
        // we listen for it and let WeixinExtendSocialite register the 'weixin' driver.
        Event::listen(SocialiteWasCalled::class, [WeixinExtendSocialite::class, 'handle']);
    }
}
