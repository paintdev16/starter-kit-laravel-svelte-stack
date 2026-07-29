<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->input('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($user) => [
                'type' => 'user',
                'label' => $user->name,
                'description' => $user->email,
                'url' => "/users/{$user->id}",
                'icon' => 'user',
            ]);

        return response()->json([
            [
                'group' => 'Usuarios',
                'items' => $users,
            ]
        ]);
    }
}
