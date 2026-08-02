<?php

namespace App\Http\Controllers\Api;

use App\Actions\Activity\GroupActivityLogs;
use App\Actions\Activity\ListActivityLogs;
use App\Http\Controllers\Controller;
use App\Services\DeviceDetectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ListActivityLogs $listActivityLogs,
        private readonly GroupActivityLogs $groupActivityLogs,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user() || ! ($request->user()->hasRole('root') || $request->user()->hasRole('super-admin'))) {
            return response()->json(['data' => [], 'message' => 'No autorizado'], 403);
        }

        $logs = ($this->listActivityLogs)(
            $request->filled('user') ? $request->integer('user') : null,
            perPage: 20,
        );

        return response()->json($logs);
    }

    public function currentDevice(Request $request): JsonResponse
    {
        return response()->json(DeviceDetectorService::fromRequest($request));
    }

    public function grouped(Request $request): JsonResponse
    {
        if (! $request->user() || ! ($request->user()->hasRole('root') || $request->user()->hasRole('super-admin'))) {
            return response()->json(['data' => [], 'message' => 'No autorizado'], 403);
        }

        return response()->json(($this->groupActivityLogs)());
    }
}
