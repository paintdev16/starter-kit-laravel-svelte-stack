<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'root'))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->currentAvatarUrl(),
                'email_verified_at' => $user->email_verified_at,
                'has_two_factor' => $user->two_factor_confirmed_at !== null,
                'has_passkeys' => $user->passkeys()->exists(),
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

        return response()->json($users);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if ($user->hasRole('root') && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->currentAvatarUrl(),
            'email_verified_at' => $user->email_verified_at,
            'has_two_factor' => $user->two_factor_confirmed_at !== null,
            'has_passkeys' => $user->passkeys()->exists(),
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if (!empty($data['role']) && $data['role'] === 'root' && User::role('root')->exists()) {
            return response()->json(['message' => 'Ya existe un usuario root.'], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['role'])) {
            $user->syncRoles($data['role']);
        }

        ActivityLoggerService::log($request, 'user.created', "Usuario \"{$user->name}\" creado");

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($user->isRoot() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Solo el propio usuario root puede modificar su cuenta.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if ($request->has('role') && $user->isRoot() && $data['role'] !== 'root') {
            return response()->json(['message' => 'No puedes cambiar el rol de un usuario root.'], 403);
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($request->has('role')) {
            $user->syncRoles($data['role'] ?? []);
        }

        ActivityLoggerService::log($request, 'user.updated', "Usuario \"{$user->name}\" actualizado");

        return response()->json($user->load('roles'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->isRoot()) {
            return response()->json(['message' => 'No puedes eliminar un usuario root.'], 403);
        }

        $userName = $user->name;
        $user->delete();

        ActivityLoggerService::log($request, 'user.deleted', "Usuario \"{$userName}\" eliminado");

        return response()->json(null, 204);
    }
}
