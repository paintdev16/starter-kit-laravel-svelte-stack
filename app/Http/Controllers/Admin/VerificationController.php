<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            abort(403);
        }

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'root'))
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'verified' => $user->email_verified_at !== null,
                'email_verified_at' => $user->email_verified_at,
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at,
            ]);

        return response()->json($users);
    }

    public function toggle(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->hasAnyRole(['root', 'super-admin'])) {
            return response()->json(['message' => 'No se puede cambiar la verificación de este usuario.'], 403);
        }

        $user->email_verified_at = $user->email_verified_at ? null : now();
        $user->save();

        return response()->json([
            'id' => $user->id,
            'verified' => $user->email_verified_at !== null,
            'email_verified_at' => $user->email_verified_at,
        ]);
    }
}
