<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Broadcast::channel('admin.users', function ($user) {
            return $user->hasAnyRole(['root', 'super-admin']);
        });
    }
}
