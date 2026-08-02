<?php

namespace Tests\Feature;

use App\Contracts\ServiceManager;
use App\Events\UserCreated;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Sleep;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('root', 'web');
        Role::findOrCreate('super-admin', 'web');
    }

    private function rootUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('root');

        return $user;
    }

    private function superAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('systems.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_the_systems_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('systems.index'))->assertForbidden();
    }

    public function test_super_admin_users_cannot_access_the_systems_page(): void
    {
        $this->actingAs($this->superAdminUser());

        $this->get(route('systems.index'))->assertForbidden();
    }

    public function test_super_admin_users_cannot_toggle_realtime(): void
    {
        $this->actingAs($this->superAdminUser());

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => true])->assertForbidden();
    }

    public function test_root_users_can_visit_the_systems_page(): void
    {
        $manager = new class implements ServiceManager
        {
            public function start(): void {}

            public function stop(): void {}

            public function restart(): void {}

            public function running(): bool
            {
                return true;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        $this->actingAs($this->rootUser());

        $this->get(route('systems.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('systems/Index')
                ->where('realtime.enabled', true)
                ->where('realtime.manager', 'local')
                ->where('general.env', app()->environment())
                ->where('general.app_name', config('app.name')));
    }

    public function test_non_admin_users_cannot_toggle_realtime(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('systems.realtime.toggle'))->assertForbidden();
    }

    public function test_root_users_can_toggle_realtime(): void
    {
        $manager = new class implements ServiceManager
        {
            public bool $started = false;

            public bool $stopped = false;

            public bool $running = false;

            public function start(): void
            {
                $this->started = true;
                $this->running = true;
            }

            public function stop(): void
            {
                $this->stopped = true;
                $this->running = false;
            }

            public function restart(): void {}

            public function running(): bool
            {
                return $this->running;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        Setting::set('realtime_enabled', 'false');

        $this->actingAs($this->rootUser());

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => true])
            ->assertOk()
            ->assertJson([
                'enabled' => true,
                'running' => true,
            ]);

        $this->assertTrue($manager->started);
        $this->assertSame('true', Setting::get('realtime_enabled'));

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => false])
            ->assertOk()
            ->assertJson([
                'enabled' => false,
                'running' => false,
            ]);

        $this->assertTrue($manager->stopped);
        $this->assertSame('false', Setting::get('realtime_enabled'));
    }

    public function test_toggle_requires_the_enabled_field(): void
    {
        $this->actingAs($this->rootUser());

        $this->postJson(route('systems.realtime.toggle'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('enabled');
    }

    public function test_toggle_waits_until_reverb_reports_running(): void
    {
        Sleep::fake();

        $manager = new class implements ServiceManager
        {
            public int $runningCalls = 0;

            public bool $started = false;

            public function start(): void
            {
                $this->started = true;
            }

            public function stop(): void {}

            public function restart(): void {}

            public function running(): bool
            {
                $this->runningCalls++;

                return $this->runningCalls >= 4;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        Setting::set('realtime_enabled', 'false');

        $this->actingAs($this->rootUser());

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => true])
            ->assertOk()
            ->assertJson([
                'enabled' => true,
                'running' => true,
            ]);

        $this->assertTrue($manager->started);
        Sleep::assertSleptTimes(3);
    }

    public function test_toggle_returns_running_false_with_error_when_service_never_starts(): void
    {
        Sleep::fake();

        $manager = new class implements ServiceManager
        {
            public function start(): void {}

            public function stop(): void {}

            public function restart(): void {}

            public function running(): bool
            {
                return false;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        Setting::set('realtime_enabled', 'false');

        $this->actingAs($this->rootUser());

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => true])
            ->assertOk()
            ->assertJsonPath('enabled', false)
            ->assertJsonPath('running', false)
            ->assertJsonStructure(['enabled', 'running', 'error']);

        $this->assertSame('false', Setting::get('realtime_enabled'));
    }

    public function test_toggle_keeps_realtime_enabled_when_the_service_keeps_running_after_stop(): void
    {
        Sleep::fake();

        $manager = new class implements ServiceManager
        {
            public function start(): void {}

            public function stop(): void {}

            public function restart(): void {}

            public function running(): bool
            {
                return true;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        Setting::set('realtime_enabled', 'true');

        $this->actingAs($this->rootUser());

        $this->postJson(route('systems.realtime.toggle'), ['enabled' => false])
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonPath('running', true)
            ->assertJsonStructure(['enabled', 'running', 'error']);

        $this->assertSame('true', Setting::get('realtime_enabled'));
    }

    public function test_status_reconciles_the_setting_when_the_service_is_down(): void
    {
        $manager = new class implements ServiceManager
        {
            public function start(): void {}

            public function stop(): void {}

            public function restart(): void {}

            public function running(): bool
            {
                return false;
            }
        };

        $this->app->instance(ServiceManager::class, $manager);

        Setting::set('realtime_enabled', 'true');

        $this->actingAs($this->rootUser());

        $this->getJson(route('systems.realtime.status'))
            ->assertOk()
            ->assertJson([
                'enabled' => false,
                'running' => false,
            ]);

        $this->assertSame('false', Setting::get('realtime_enabled'));
    }

    public function test_user_created_event_is_not_broadcast_when_realtime_is_disabled(): void
    {
        Event::fake([UserCreated::class]);

        Setting::set('realtime_enabled', 'false');
        User::factory()->create();

        Event::assertNotDispatched(UserCreated::class);

        Setting::set('realtime_enabled', 'true');
        User::factory()->create();

        Event::assertDispatched(UserCreated::class);
    }
}
