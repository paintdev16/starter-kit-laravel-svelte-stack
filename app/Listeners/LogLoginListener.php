<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Services\DeviceDetectorService;
use Illuminate\Auth\Events\Login;

class LogLoginListener
{
    public function handle(Login $event): void
    {
        if (! $event->user) {
            return;
        }

        $deviceInfo = DeviceDetectorService::fromRequest(request());

        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'auth.login',
            'description' => "Inicio de sesión: \"{$event->user->name}\"",
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
