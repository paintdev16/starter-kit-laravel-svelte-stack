<?php

namespace App\Actions\Activity;

use App\Services\ActivityService;

class GroupActivityLogs
{
    public function __construct(private ActivityService $activity) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return $this->activity->grouped();
    }
}
