<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            OauthProviderSeeder::class,
        ]);

        $root = User::factory()->create([
            'name' => 'paint',
            'email' => 'paint@gmail.com',
            'password' => Hash::make('123456789'),
        ]);

        $root->assignRole('root');

        $admin = User::factory()->create([
            'name' => 'Kelly',
            'email' => 'kelly@gmail.com',
            'password' => Hash::make('123456789'),
        ]);

        $admin->assignRole('admin');
    }
}
