<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Telegram::class, function ($app) {
            return new Telegram(config('telegrambot.api_key'), config('telegrambot.username'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
