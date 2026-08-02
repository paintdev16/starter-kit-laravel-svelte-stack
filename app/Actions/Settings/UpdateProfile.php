<?php

namespace App\Actions\Settings;

use App\Models\User;
use App\Services\SettingsService;

class UpdateProfile
{
    public function __construct(private SettingsService $settings) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(User $user, array $data): void
    {
        $this->settings->updateProfile($user, $data);
    }
}
