<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(int $excludeUserId): LengthAwarePaginator
    {
        return User::query()
            ->where('id', '!=', $excludeUserId)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'root'))
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles($data['role']);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?bool $verified = null): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if (array_key_exists('role', $data)) {
            $user->syncRoles($data['role'] ?? []);
        }

        if ($verified !== null) {
            $user->email_verified_at = $verified ? now() : null;
            $user->save();
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $search): array
    {
        if (strlen($search) < 2) {
            return [];
        }

        $users = User::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'type' => 'user',
                'label' => $user->name,
                'description' => $user->email,
                'url' => "/users/{$user->id}",
                'icon' => 'user',
            ]);

        return [
            [
                'group' => 'Usuarios',
                'items' => $users,
            ],
        ];
    }

    public function toggleVerification(User $user): User
    {
        $user->email_verified_at = $user->email_verified_at ? null : now();
        $user->save();

        return $user;
    }
}
