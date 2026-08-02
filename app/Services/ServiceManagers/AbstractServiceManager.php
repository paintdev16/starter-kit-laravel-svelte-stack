<?php

namespace App\Services\ServiceManagers;

use App\Contracts\ServiceManager;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use RuntimeException;

abstract class AbstractServiceManager implements ServiceManager
{
    abstract public function start(): void;

    abstract public function stop(): void;

    abstract public function restart(): void;

    public function running(): bool
    {
        $connection = @fsockopen('127.0.0.1', $this->port(), $errno, $errstr, 0.5);

        if ($connection === false) {
            return false;
        }

        fclose($connection);

        return true;
    }

    protected function port(): int
    {
        return (int) config('reverb.servers.reverb.port', 8080);
    }

    protected function serviceName(): string
    {
        return (string) config('services.realtime_service', 'reverb');
    }

    protected function phpBinary(): string
    {
        return PHP_BINARY;
    }

    protected function rootPath(): string
    {
        return base_path();
    }

    protected function logPath(): string
    {
        return storage_path('logs/reverb.log');
    }

    /**
     * @param  list<string>  $command
     */
    protected function run(array $command, int $timeout = 60): ProcessResult
    {
        $result = Process::timeout($timeout)->run($command);

        if ($result->failed()) {
            throw new RuntimeException(
                'Comando fallido ['.implode(' ', $command).'] (exit '.$result->exitCode().'): '.trim($result->errorOutput()),
            );
        }

        return $result;
    }
}
