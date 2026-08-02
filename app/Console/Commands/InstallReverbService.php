<?php

namespace App\Console\Commands;

use App\Contracts\InstallableServiceManager;
use App\Services\ServiceManagerFactory;
use Illuminate\Console\Command;
use Throwable;

class InstallReverbService extends Command
{
    protected $signature = 'systems:install-reverb';

    protected $description = 'Instala la unit de systemd o el programa de supervisor para el servicio en tiempo real.';

    public function handle(ServiceManagerFactory $factory): int
    {
        $driver = (string) config('services.manager', 'local');

        if ($driver === 'local') {
            $this->warn('SERVICE_MANAGER es "local": el proceso se lanza directamente y no requiere instalación.');

            return self::SUCCESS;
        }

        $manager = $factory->make($driver);

        if (! $manager instanceof InstallableServiceManager) {
            $this->error("El driver [{$driver}] no soporta instalación.");

            return self::FAILURE;
        }

        try {
            $manager->install();
        } catch (Throwable $e) {
            $this->error('No se pudo instalar el servicio: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Servicio instalado correctamente.');

        $this->components->bulletList([
            'Verifica el estado con: '.$this->statusCommand(),
            'Arranca el servicio con: '.$this->startCommand(),
        ]);

        return self::SUCCESS;
    }

    protected function statusCommand(): string
    {
        if ((string) config('services.manager', 'local') === 'supervisor') {
            return 'supervisorctl status';
        }

        return 'systemctl status '.config('services.systemd.unit');
    }

    protected function startCommand(): string
    {
        if ((string) config('services.manager', 'local') === 'supervisor') {
            return 'supervisorctl start '.config('services.supervisor.program');
        }

        return 'systemctl start '.config('services.systemd.unit');
    }
}
