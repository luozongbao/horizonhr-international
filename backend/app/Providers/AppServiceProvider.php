<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
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
        // Register the WeChat (Weixin) Socialite driver from SocialiteProviders/WeChat.
        // This dispatches the SocialiteWasCalled event which the provider listens for.
        $socialite = $this->app->make(Factory::class);
        $event     = new SocialiteWasCalled($socialite);
        (new WeixinExtendSocialite)->handle($event);
    }
}
