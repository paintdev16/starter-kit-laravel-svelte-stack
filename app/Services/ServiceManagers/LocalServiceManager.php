<?php

namespace App\Services\ServiceManagers;

use App\Contracts\ServiceManager;
use Illuminate\Support\Sleep;

class LocalServiceManager implements ServiceManager
{
    public function start(): void
    {
        if ($this->running()) {
            return;
        }

        $logPath = storage_path('logs/reverb.log');

        if (! is_dir((string) dirname($logPath))) {
            mkdir((string) dirname($logPath), 0775, true);
        }

        $port = $this->port();
        $php = PHP_BINARY;
        $root = base_path();

        $command = windows_os()
            ? sprintf('cmd /c start "" /D "%s" /B "%s" artisan reverb:start --host=127.0.0.1 --port=%d > "%s" 2>&1', $root, $php, $port, $logPath)
            : sprintf('cd "%s" && nohup "%s" artisan reverb:start --host=127.0.0.1 --port=%d > "%s" 2>&1 &', $root, $php, $port, $logPath);

        $this->launchDetached($command);
    }

    public function stop(): void
    {
        $port = $this->port();

        foreach ($this->pidsOnPort($port) as $pid) {
            if ($pid <= 0) {
                continue;
            }

            $this->launchDetached(windows_os()
                ? 'taskkill /F /PID '.$pid
                : 'kill -TERM '.$pid);
        }

        if (windows_os()) {
            return;
        }

        if ($this->running()) {
            Sleep::usleep(500_000);

            foreach ($this->pidsOnPort($port) as $pid) {
                if ($pid <= 0) {
                    continue;
                }

                $this->launchDetached('kill -9 '.$pid);
            }
        }
    }

    public function restart(): void
    {
        $this->stop();
        $this->start();
    }

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

    protected function launchDetached(string $command): void
    {
        $handle = @popen($command, 'r');

        if ($handle !== false) {
            pclose($handle);
        }
    }

    /**
     * @return list<int>
     */
    protected function pidsOnPort(int $port): array
    {
        return windows_os() ? $this->windowsPidsOnPort($port) : $this->unixPidsOnPort($port);
    }

    /**
     * @return list<int>
     */
    protected function windowsPidsOnPort(int $port): array
    {
        $output = (string) shell_exec('netstat -ano');

        $pids = [];
        $suffix = ':'.$port;

        foreach (explode("\n", $output) as $line) {
            $segments = preg_split('/\s+/', trim($line));

            if ($segments === false || count($segments) < 5) {
                continue;
            }

            if ($segments[0] !== 'TCP' || $segments[3] !== 'LISTENING') {
                continue;
            }

            if (! str_ends_with($segments[1], $suffix)) {
                continue;
            }

            $pid = (int) $segments[4];

            if ($pid > 0) {
                $pids[] = $pid;
            }
        }

        return array_values(array_unique($pids));
    }

    /**
     * @return list<int>
     */
    protected function unixPidsOnPort(int $port): array
    {
        $output = (string) shell_exec("lsof -ti tcp:{$port} 2>/dev/null");

        $split = preg_split('/\s+/', trim($output));

        if ($split === false) {
            return [];
        }

        $pids = [];

        foreach ($split as $pid) {
            $pid = (int) $pid;

            if ($pid > 0) {
                $pids[] = $pid;
            }
        }

        return array_values(array_unique($pids));
    }
}
