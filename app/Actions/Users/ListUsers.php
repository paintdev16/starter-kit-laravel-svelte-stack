<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUsers
{
    public function __construct(private UserService $users) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function __invoke(int $excludeUserId): LengthAwarePaginator
    {
        return $this->users->paginate($excludeUserId);
    }
}
