<?php

namespace App\Observers;

use App\Events\UserCreated;
use App\Models\Setting;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (Setting::get('realtime_enabled', 'true') !== 'true') {
            return;
        }

        UserCreated::dispatch($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
