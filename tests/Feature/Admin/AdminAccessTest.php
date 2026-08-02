<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view-roles', 'create-roles', 'edit-roles', 'delete-roles'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $root = Role::findOrCreate('root', 'web');
        $root->syncPermissions(Permission::all());

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(['view-roles']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_plain_users_cannot_list_roles(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.roles.index'))->assertForbidden();
        $this->get(route('admin.permissions.index'))->assertForbidden();
    }

    public function test_admins_can_view_roles_but_not_mutate_them(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->get(route('admin.roles.index'))->assertOk();
        $this->get(route('admin.permissions.index'))->assertOk();

        $this->postJson(route('admin.roles.store'), ['name' => 'editor'])->assertForbidden();
    }

    public function test_admins_cannot_update_or_delete_roles(): void
    {
        $role = Role::findOrCreate('editor', 'web');

        $this->actingAs($this->userWithRole('admin'));

        $this->putJson(route('admin.roles.update', $role), ['name' => 'revisor'])->assertForbidden();
        $this->deleteJson(route('admin.roles.destroy', $role))->assertForbidden();
    }

    public function test_admins_cannot_mutate_permissions(): void
    {
        $permission = Permission::findOrCreate('foo-bar', 'web');

        $this->actingAs($this->userWithRole('admin'));

        $this->postJson(route('admin.permissions.store'), ['name' => 'baz'])->assertForbidden();
        $this->putJson(route('admin.permissions.update', $permission), ['name' => 'bar-foo'])->assertForbidden();
        $this->deleteJson(route('admin.permissions.destroy', $permission))->assertForbidden();
    }

    public function test_root_users_can_manage_roles_and_permissions(): void
    {
        $this->actingAs($this->userWithRole('root'));

        $this->postJson(route('admin.roles.store'), ['name' => 'editor'])
            ->assertCreated()
            ->assertJsonPath('name', 'editor');

        $this->postJson(route('admin.permissions.store'), ['name' => 'foo-bar'])
            ->assertCreated()
            ->assertJsonPath('name', 'foo-bar');
    }

    public function test_super_admin_users_cannot_access_oauth_providers(): void
    {
        $this->actingAs($this->userWithRole('super-admin'));

        $this->get(route('admin.oauth-providers.index'))->assertForbidden();
        $this->postJson(route('admin.oauth-providers.store'), [
            'provider' => 'github',
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect_uri' => 'https://example.com/callback',
        ])->assertForbidden();
    }

    public function test_root_users_can_access_oauth_providers(): void
    {
        $this->actingAs($this->userWithRole('root'));

        $this->get(route('admin.oauth-providers.index'))->assertOk();

        $this->postJson(route('admin.oauth-providers.store'), [
            'provider' => 'github',
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect_uri' => 'https://example.com/callback',
        ])->assertCreated();
    }
}
