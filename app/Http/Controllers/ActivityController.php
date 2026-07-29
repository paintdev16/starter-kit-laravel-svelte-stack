<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\DeviceDetectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! ($user->hasRole('root') || $user->hasRole('super-admin'))) {
            return response()->json(['data' => [], 'message' => 'No autorizado'], 403);
        }

        $query = ActivityLog::query()
            ->with('user:id,name');

        if ($request->filled('user')) {
            $query->where('user_id', $request->integer('user'));
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    public function currentDevice(Request $request): JsonResponse
    {
        $deviceInfo = DeviceDetectorService::fromRequest($request);

        return response()->json($deviceInfo);
    }

    public function showActivity(Request $request): Response
    {
        $user = $request->user();

        if (! $user || ! ($user->hasRole('root') || $user->hasRole('super-admin'))) {
            abort(403);
        }

        $query = ActivityLog::query()->with('user:id,name');

        if ($request->filled('user')) {
            $query->where('user_id', $request->integer('user'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30);

        return Inertia::render('users/Activity', [
            'logs' => $logs,
            'filterUserId' => $request->integer('user'),
        ]);
    }

    public function grouped(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! ($user->hasRole('root') || $user->hasRole('super-admin'))) {
            return response()->json(['data' => [], 'message' => 'No autorizado'], 403);
        }

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->groupBy(fn ($log) => $log->user_id.'_'.($log->user->name ?? 'Desconocido'))
            ->map(function ($items, $key) {
                $first = $items->first();
                $lastLogin = $items->firstWhere('action', 'auth.login')?->created_at;

                return [
                    'user_id' => $first->user_id,
                    'user_name' => $first->user->name ?? 'Desconocido',
                    'count' => $items->count(),
                    'last_action' => $first->description ?? $first->action,
                    'last_date' => $first->created_at,
                    'last_login' => $lastLogin,
                    'device_type' => $first->device_type,
                ];
            })
            ->values();

        $userIds = $logs->pluck('user_id')->filter()->unique()->values()->toArray();
        $onlineUsers = [];
        if (! empty($userIds)) {
            $fiveMinutesAgo = time() - 300;
            $onlineUsers = DB::table('sessions')
                ->whereIn('user_id', $userIds)
                ->where('last_activity', '>=', $fiveMinutesAgo)
                ->pluck('user_id')
                ->toArray();
        }
        $onlineUsers = array_flip($onlineUsers);

        $logs = $logs->map(function ($group) use ($onlineUsers) {
            return array_merge($group, [
                'is_online' => isset($onlineUsers[$group['user_id']]),
            ]);
        });

        return response()->json($logs);
    }
}
