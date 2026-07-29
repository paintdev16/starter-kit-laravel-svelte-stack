<?php

use App\Providers\AppServiceProvider;
use App\Providers\BroadcastServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    BroadcastServiceProvider::class,
    EventServiceProvider::class,
    FortifyServiceProvider::class,
];
