<?php

namespace App\Actions\Users;

use App\Services\UserService;

class SearchUsers
{
    public function __construct(private UserService $users) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(string $search): array
    {
        return $this->users->search($search);
    }
}
