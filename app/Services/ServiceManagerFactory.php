<?php

namespace App\Services;

use App\Contracts\ServiceManager;
use App\Services\ServiceManagers\LocalServiceManager;
use App\Services\ServiceManagers\SupervisorServiceManager;
use App\Services\ServiceManagers\SystemdServiceManager;
use InvalidArgumentException;

class ServiceManagerFactory
{
    public function make(?string $driver = null): ServiceManager
    {
        $driver ??= config('services.manager', 'local');

        return match ($driver) {
            'local' => new LocalServiceManager,
            'systemd' => new SystemdServiceManager,
            'supervisor' => new SupervisorServiceManager,
            default => throw new InvalidArgumentException("Unsupported service manager driver [{$driver}]."),
        };
    }
}
