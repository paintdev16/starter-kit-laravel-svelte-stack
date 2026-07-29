<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLoggerService
{
    public static function log(Request $request, string $action, ?string $description = null): ActivityLog
    {
        $deviceInfo = DeviceDetectorService::fromRequest($request);

        return ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'description' => $description,
            ...$deviceInfo,
        ]);
    }
}
