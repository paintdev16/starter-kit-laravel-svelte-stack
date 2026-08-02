<?php

namespace Tests\Feature;

use App\Services\ServiceManagers\SupervisorServiceManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class SupervisorServiceManagerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/reverb-supervisor-'.uniqid();
        config(['services.supervisor.directory' => $this->tempDir]);
        config(['services.supervisor.program' => 'reverb']);
        config(['services.supervisor.sudo' => false]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    public function test_start_installs_the_config_then_starts_it(): void
    {
        Process::fake();

        (new SupervisorServiceManager)->start();

        $configPath = $this->tempDir.'/reverb.conf';

        $this->assertFileExists($configPath);
        $this->assertStringContainsString('[program:reverb]', File::get($configPath));
        $this->assertStringContainsString('autostart=false', File::get($configPath));

        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'reread']);
        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'update']);
        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'start', 'reverb']);
    }

    public function test_stop_and_restart_run_supervisorctl(): void
    {
        Process::fake();

        $manager = new SupervisorServiceManager;

        $manager->stop();
        $manager->restart();

        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'stop', 'reverb']);
        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'restart', 'reverb']);
    }

    public function test_commands_are_prefixed_with_sudo_when_enabled(): void
    {
        config(['services.supervisor.sudo' => true]);

        Process::fake();

        (new SupervisorServiceManager)->stop();

        Process::assertRan(fn ($process) => $process->command === ['sudo', '-n', 'supervisorctl', 'stop', 'reverb']);
    }

    public function test_it_throws_when_supervisorctl_fails(): void
    {
        Process::fake([
            '*' => fn ($process) => $process->command === ['supervisorctl', 'start', 'reverb']
                ? Process::result(errorOutput: 'ERROR (no such process)', exitCode: 1)
                : Process::result(),
        ]);

        $this->expectException(RuntimeException::class);

        (new SupervisorServiceManager)->start();
    }

    public function test_install_reverb_command_installs_the_supervisor_config(): void
    {
        config(['services.manager' => 'supervisor']);

        Process::fake();

        $this->artisan('systems:install-reverb')->assertSuccessful();

        $this->assertFileExists($this->tempDir.'/reverb.conf');
        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'reread']);
        Process::assertRan(fn ($process) => $process->command === ['supervisorctl', 'update']);
    }
}
