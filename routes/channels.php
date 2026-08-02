<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.users', function (User $user) {
    return $user->hasAnyRole(['root', 'super-admin']);
});
