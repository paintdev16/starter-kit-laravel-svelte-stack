<?php

namespace App\Actions\Settings;

use App\Models\User;
use App\Services\SettingsService;

class DeleteProfile
{
    public function __construct(private SettingsService $settings) {}

    public function __invoke(User $user): void
    {
        $this->settings->deleteProfile($user);
    }
}
