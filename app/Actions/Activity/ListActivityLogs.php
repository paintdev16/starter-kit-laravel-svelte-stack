<?php

namespace App\Actions\Activity;

use App\Models\ActivityLog;
use App\Services\ActivityService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListActivityLogs
{
    public function __construct(private ActivityService $activity) {}

    /**
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function __invoke(?int $userId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activity->paginate($userId, $perPage);
    }
}
