<?php

namespace App\Actions\Settings;

use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class UpdatePassword
{
    public function __construct(private SettingsService $settings) {}

    public function __invoke(Request $request, User $user, string $password): void
    {
        $this->settings->updatePassword($user, $password);

        ActivityLoggerService::log($request, 'auth.password_changed', "Contraseña cambiada: \"{$user->name}\"");
    }
}
