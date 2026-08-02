<?php

namespace App\Http\Controllers\Api;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ListUsers;
use App\Actions\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use App\Support\UserPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(
        private readonly ListUsers $listUsers,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeleteUser $deleteUser,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            ($this->listUsers)($request->user()->id)->through(fn (User $user): array => UserPresenter::toArray($user)),
        );
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if ($user->hasRole('root') && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json(UserPresenter::toArray($user));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validated();

        if (! empty($data['role']) && $data['role'] === 'root' && User::role('root')->exists()) {
            return response()->json(['message' => 'Ya existe un usuario root.'], 422);
        }

        $user = ($this->createUser)($request, $data);

        return response()->json($user->load('roles'), 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])
            && $request->user()->id !== $user->id
        ) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isRoot() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Solo el propio usuario root puede modificar su cuenta.'], 403);
        }

        $data = $request->validated();

        if ($request->has('role') && $user->isRoot() && $data['role'] !== 'root') {
            return response()->json(['message' => 'No puedes cambiar el rol de un usuario root.'], 403);
        }

        $verified = null;

        if ($request->has('verified') && $request->user()->hasAnyRole(['root', 'super-admin'])) {
            $verified = $request->boolean('verified');
        }

        $user = ($this->updateUser)($request, $user, $data, $verified);

        return response()->json($user->load('roles'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isRoot()) {
            return response()->json(['message' => 'No puedes eliminar un usuario root.'], 403);
        }

        ($this->deleteUser)($request, $user);

        return response()->json(null, 204);
    }
}
