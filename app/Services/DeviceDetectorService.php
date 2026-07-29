<?php

namespace App\Services;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;

class DeviceDetectorService
{
    /** @return array<string, mixed> */
    public static function fromRequest(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        $clientHints = ClientHints::factory($request->server());

        $dd = new DeviceDetector($userAgent, $clientHints);
        $dd->parse();

        $os = $dd->getOs();
        $browser = $dd->getClient();
        $device = $dd->getDeviceName();

        return [
            'user_agent' => $userAgent,
            'ip_address' => $request->ip(),
            'device_type' => $device ? ucfirst($device) : null,
            'device_brand' => $dd->getBrandName(),
            'device_model' => $dd->getModel(),
            'os' => $os['name'] ?? null,
            'os_version' => $os['version'] ?? null,
            'browser' => $browser['name'] ?? null,
            'browser_version' => $browser['version'] ?? null,
        ];
    }
}
