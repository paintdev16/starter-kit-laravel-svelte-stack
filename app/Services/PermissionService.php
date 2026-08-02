<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Permission::all()->map(fn (Permission $permission): array => [
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        $permission = Permission::query()->create(['name' => $data['name'], 'guard_name' => 'web']);

        $root = Role::where('name', 'root')->first();

        if ($root) {
            $root->givePermissionTo($permission);
        }

        return $permission;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update(['name' => $data['name']]);

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
