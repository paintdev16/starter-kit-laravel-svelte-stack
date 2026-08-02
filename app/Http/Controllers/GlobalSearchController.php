<?php

namespace App\Http\Controllers;

use App\Actions\Users\SearchUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(
        private readonly SearchUsers $searchUsers,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(($this->searchUsers)($request->input('q', '')));
    }
}
