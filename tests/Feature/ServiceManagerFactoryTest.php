<?php

namespace Tests\Feature;

use App\Services\ServiceManagerFactory;
use App\Services\ServiceManagers\LocalServiceManager;
use App\Services\ServiceManagers\SupervisorServiceManager;
use App\Services\ServiceManagers\SystemdServiceManager;
use InvalidArgumentException;
use Tests\TestCase;

class ServiceManagerFactoryTest extends TestCase
{
    public function test_it_builds_the_supported_drivers(): void
    {
        $factory = new ServiceManagerFactory;

        $this->assertInstanceOf(LocalServiceManager::class, $factory->make('local'));
        $this->assertInstanceOf(SystemdServiceManager::class, $factory->make('systemd'));
        $this->assertInstanceOf(SupervisorServiceManager::class, $factory->make('supervisor'));
    }

    public function test_it_uses_the_configured_driver_by_default(): void
    {
        config(['services.manager' => 'systemd']);

        $this->assertInstanceOf(SystemdServiceManager::class, (new ServiceManagerFactory)->make());
    }

    public function test_it_rejects_unknown_drivers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ServiceManagerFactory)->make('docker');
    }
}
