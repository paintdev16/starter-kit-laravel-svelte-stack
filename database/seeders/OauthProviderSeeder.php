<?php

namespace Database\Seeders;

use App\Models\OauthProvider;
use Illuminate\Database\Seeder;

class OauthProviderSeeder extends Seeder
{
    public function run(): void
    {
        OauthProvider::firstOrCreate(
            ['provider' => 'google'],
            [
                'client_id' => '',
                'client_secret' => '',
                'redirect_uri' => '/auth/google/callback',
                'enabled' => false,
                'sort' => 1,
            ]
        );

        OauthProvider::firstOrCreate(
            ['provider' => 'github'],
            [
                'client_id' => '',
                'client_secret' => '',
                'redirect_uri' => '/auth/github/callback',
                'enabled' => false,
                'sort' => 2,
            ]
        );
    }
}
