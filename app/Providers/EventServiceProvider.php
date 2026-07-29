<?php

namespace App\Providers;

use App\Listeners\LogLoginListener;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogLoginListener::class,
        ],
    ];

    protected $observers = [
        User::class => [UserObserver::class],
    ];
}
