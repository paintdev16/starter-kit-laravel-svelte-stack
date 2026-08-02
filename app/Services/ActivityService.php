<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    /**
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function paginate(?int $userId = null, int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function grouped(): array
    {
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

        return $logs->map(function ($group) use ($onlineUsers) {
            return array_merge($group, [
                'is_online' => isset($onlineUsers[$group['user_id']]),
            ]);
        })->all();
    }
}
