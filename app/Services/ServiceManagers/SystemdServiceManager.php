<?php

namespace App\Services\ServiceManagers;

use App\Contracts\InstallableServiceManager;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;

class SystemdServiceManager extends AbstractServiceManager implements InstallableServiceManager
{
    public function start(): void
    {
        $this->install();
        $this->systemctl(['start', $this->unitName()]);
    }

    public function stop(): void
    {
        $this->systemctl(['stop', $this->unitName()]);
    }

    public function restart(): void
    {
        $this->systemctl(['restart', $this->unitName()]);
    }

    public function install(): void
    {
        if (File::exists($this->unitPath())) {
            return;
        }

        File::ensureDirectoryExists(dirname($this->unitPath()));
        File::put($this->unitPath(), $this->unitContents());

        $this->systemctl(['daemon-reload']);
        $this->systemctl(['enable', $this->unitName()]);
    }

    protected function unitName(): string
    {
        return (string) config('services.systemd.unit', $this->serviceName());
    }

    protected function unitPath(): string
    {
        $directory = rtrim((string) config('services.systemd.directory', '/etc/systemd/system'), '/\\');

        return $directory.'/'.$this->unitName().'.service';
    }

    protected function unitContents(): string
    {
        $user = (string) config('services.systemd.user', '');

        $lines = [
            '[Unit]',
            'Description='.$this->serviceName().' WebSocket server',
            'After=network.target',
            '',
            '[Service]',
            'WorkingDirectory='.$this->rootPath(),
            'ExecStart='.$this->phpBinary().' artisan reverb:start --host=127.0.0.1 --port='.$this->port(),
            'Restart=on-failure',
        ];

        if ($user !== '') {
            $lines[] = 'User='.$user;
        }

        $lines[] = '';
        $lines[] = '[Install]';
        $lines[] = 'WantedBy=multi-user.target';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    /**
     * @param  list<string>  $args
     */
    protected function systemctl(array $args): ProcessResult
    {
        $command = ['systemctl', ...$args];

        if (config('services.systemd.sudo', false)) {
            $command = ['sudo', '-n', ...$command];
        }

        return $this->run($command);
    }
}
