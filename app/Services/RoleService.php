<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Role::with('permissions')->get()->map(fn (Role $role): array => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        $role = Role::query()->create(['name' => $data['name'], 'guard_name' => 'web']);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
