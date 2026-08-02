<?php

namespace App\Services\ServiceManagers;

use App\Contracts\InstallableServiceManager;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;

class SupervisorServiceManager extends AbstractServiceManager implements InstallableServiceManager
{
    public function start(): void
    {
        $this->install();
        $this->supervisorctl(['start', $this->programName()]);
    }

    public function stop(): void
    {
        $this->supervisorctl(['stop', $this->programName()]);
    }

    public function restart(): void
    {
        $this->supervisorctl(['restart', $this->programName()]);
    }

    public function install(): void
    {
        if (File::exists($this->configPath())) {
            return;
        }

        File::ensureDirectoryExists(dirname($this->configPath()));
        File::put($this->configPath(), $this->configContents());

        $this->supervisorctl(['reread']);
        $this->supervisorctl(['update']);
    }

    protected function programName(): string
    {
        return (string) config('services.supervisor.program', $this->serviceName());
    }

    protected function configPath(): string
    {
        $directory = rtrim((string) config('services.supervisor.directory', '/etc/supervisor/conf.d'), '/\\');

        return $directory.'/'.$this->programName().'.conf';
    }

    protected function configContents(): string
    {
        $log = $this->logPath();

        $lines = [
            '[program:'.$this->programName().']',
            'process_name=%(program_name)s',
            'command='.$this->phpBinary().' '.$this->rootPath().'/artisan reverb:start --host=127.0.0.1 --port='.$this->port(),
            'directory='.$this->rootPath(),
            'autostart=false',
            'autorestart=true',
            'redirect_stderr=true',
            'stdout_logfile='.$log,
            'stderr_logfile='.$log,
            'stopasgroup=true',
            'killasgroup=true',
            'numprocs=1',
        ];

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    /**
     * @param  list<string>  $args
     */
    protected function supervisorctl(array $args): ProcessResult
    {
        $command = ['supervisorctl', ...$args];

        if (config('services.supervisor.sudo', false)) {
            $command = ['sudo', '-n', ...$command];
        }

        return $this->run($command);
    }
}
