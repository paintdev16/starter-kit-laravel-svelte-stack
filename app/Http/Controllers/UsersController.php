<?php

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\ListUsers;
use App\Actions\Users\UpdateUser;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use App\Support\UserPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function __construct(
        private readonly ListUsers $listUsers,
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeleteUser $deleteUser,
    ) {}

    public function index(Request $request): Response
    {
        $users = ($this->listUsers)($request->user()->id)
            ->through(fn (User $user): array => UserPresenter::toArray($user));

        $rootCount = User::role('root')->count();
        $canViewActivity = $request->user()->hasRole('root') || $request->user()->hasRole('super-admin');
        $canManageTokens = $request->user()->hasRole('root') || $request->user()->hasRole('super-admin');
        $canCreateUser = $request->user()->hasAnyRole(['root', 'super-admin']);
        $canManageVerification = $request->user()->hasAnyRole(['root', 'super-admin']);
        $canViewRoles = $request->user()->can('view-roles');

        return Inertia::render('users/Index', [
            'users' => $users,
            'rootCount' => $rootCount,
            'canViewActivity' => $canViewActivity,
            'canManageTokens' => $canManageTokens,
            'canCreateUser' => $canCreateUser,
            'canManageVerification' => $canManageVerification,
            'canViewRoles' => $canViewRoles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            abort(403, 'No tienes permiso para crear usuarios.');
        }

        $data = $request->validated();

        if (! empty($data['role']) && $data['role'] === 'root' && User::role('root')->exists()) {
            return redirect()->route('users.index')->withErrors(['role' => 'Ya existe un usuario root.']);
        }

        ($this->createUser)($request, $data);

        return redirect()->route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])
            && $request->user()->id !== $user->id
        ) {
            abort(403, 'No tienes permiso para modificar usuarios.');
        }

        if ($user->isRoot() && $request->user()->id !== $user->id) {
            return redirect()->route('users.index')->withErrors(['general' => 'Solo el propio usuario root puede modificar su cuenta.']);
        }

        $data = $request->validated();

        if ($request->has('role') && $user->isRoot() && $data['role'] !== 'root') {
            return redirect()->route('users.index')->withErrors(['role' => 'No puedes cambiar el rol de un usuario root.']);
        }

        $verified = null;

        if ($request->has('verified') && $request->user()->hasAnyRole(['root', 'super-admin'])) {
            $verified = $request->boolean('verified');
        }

        ($this->updateUser)($request, $user, $data, $verified);

        return redirect()->route('users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }

        if ($user->isRoot()) {
            return redirect()->route('users.index')->withErrors(['general' => 'No puedes eliminar un usuario root.']);
        }

        ($this->deleteUser)($request, $user);

        return redirect()->route('users.index');
    }
}
