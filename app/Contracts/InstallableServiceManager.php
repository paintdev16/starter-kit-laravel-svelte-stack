<?php

namespace App\Contracts;

interface InstallableServiceManager
{
    /**
     * Write the unit/config file for the managed service and (re)load it
     * into the service manager (daemon-reload / reread + update).
     */
    public function install(): void;
}
