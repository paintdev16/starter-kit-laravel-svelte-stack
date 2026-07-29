<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\DeviceDetectorService;
use Illuminate\Auth\Events\Login;

class LogLoginListener
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $deviceInfo = DeviceDetectorService::fromRequest(request());

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'auth.login',
            'description' => "Inicio de sesión: \"{$user->name}\"",
            'user_agent' => $deviceInfo['user_agent'],
            'ip_address' => $deviceInfo['ip_address'],
            'device_type' => $deviceInfo['device_type'],
            'device_brand' => $deviceInfo['device_brand'],
            'device_model' => $deviceInfo['device_model'],
            'os' => $deviceInfo['os'],
            'os_version' => $deviceInfo['os_version'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
        ]);
    }
}
