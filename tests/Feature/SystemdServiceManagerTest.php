<?php

namespace Tests\Feature;

use App\Services\ServiceManagers\SystemdServiceManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class SystemdServiceManagerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/reverb-systemd-'.uniqid();
        config(['services.systemd.directory' => $this->tempDir]);
        config(['services.systemd.unit' => 'reverb']);
        config(['services.systemd.sudo' => false]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    public function test_start_installs_the_unit_then_starts_it(): void
    {
        Process::fake();

        (new SystemdServiceManager)->start();

        $unitPath = $this->tempDir.'/reverb.service';

        $this->assertFileExists($unitPath);
        $this->assertStringContainsString('[Service]', File::get($unitPath));
        $this->assertStringContainsString('ExecStart=', File::get($unitPath));

        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'daemon-reload']);
        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'enable', 'reverb']);
        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'start', 'reverb']);
    }

    public function test_start_skips_installation_when_the_unit_already_exists(): void
    {
        File::makeDirectory($this->tempDir, 0777, true, true);
        File::put($this->tempDir.'/reverb.service', "[Unit]\n");

        Process::fake();

        (new SystemdServiceManager)->start();

        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'start', 'reverb']);
        Process::assertNotRan(fn ($process) => $process->command === ['systemctl', 'daemon-reload']);
        Process::assertNotRan(fn ($process) => $process->command === ['systemctl', 'enable', 'reverb']);
    }

    public function test_stop_and_restart_run_systemctl(): void
    {
        Process::fake();

        $manager = new SystemdServiceManager;

        $manager->stop();
        $manager->restart();

        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'stop', 'reverb']);
        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'restart', 'reverb']);
    }

    public function test_commands_are_prefixed_with_sudo_when_enabled(): void
    {
        config(['services.systemd.sudo' => true]);

        Process::fake();

        (new SystemdServiceManager)->stop();

        Process::assertRan(fn ($process) => $process->command === ['sudo', '-n', 'systemctl', 'stop', 'reverb']);
    }

    public function test_it_throws_when_systemctl_fails(): void
    {
        Process::fake([
            '*' => fn ($process) => $process->command === ['systemctl', 'start', 'reverb']
                ? Process::result(errorOutput: 'Permission denied', exitCode: 1)
                : Process::result(),
        ]);

        $this->expectException(RuntimeException::class);

        (new SystemdServiceManager)->start();
    }

    public function test_install_reverb_command_installs_the_systemd_unit(): void
    {
        config(['services.manager' => 'systemd']);

        Process::fake();

        $this->artisan('systems:install-reverb')->assertSuccessful();

        $this->assertFileExists($this->tempDir.'/reverb.service');
        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'daemon-reload']);
        Process::assertRan(fn ($process) => $process->command === ['systemctl', 'enable', 'reverb']);
    }
}
