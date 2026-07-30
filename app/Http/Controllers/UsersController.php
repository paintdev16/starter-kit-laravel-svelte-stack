<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'root'))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn ($user) => [
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

        $rootCount = User::role('root')->count();
        $canViewActivity = $request->user()->hasRole('root') || $request->user()->hasRole('super-admin');
        $canManageTokens = $request->user()->hasRole('root') || $request->user()->hasRole('super-admin');
        $canCreateUser = $request->user()->hasAnyRole(['root', 'super-admin']);
        $canManageVerification = $request->user()->hasAnyRole(['root', 'super-admin']);
        $canManageSocialite = $request->user()->can('socialite-manage');

        return Inertia::render('users/Index', [
            'users' => $users,
            'rootCount' => $rootCount,
            'canViewActivity' => $canViewActivity,
            'canManageTokens' => $canManageTokens,
            'canCreateUser' => $canCreateUser,
            'canManageVerification' => $canManageVerification,
            'canManageSocialite' => $canManageSocialite,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])) {
            abort(403, 'No tienes permiso para crear usuarios.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if (! empty($data['role']) && $data['role'] === 'root' && User::role('root')->exists()) {
            return redirect()->route('users.index')->withErrors(['role' => 'Ya existe un usuario root.']);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles($data['role']);
        }

        ActivityLoggerService::log($request, 'user.created', "Usuario \"{$user->name}\" creado");

        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if (! $request->user()->hasAnyRole(['root', 'super-admin'])
            && $request->user()->id !== $user->id
        ) {
            abort(403, 'No tienes permiso para modificar usuarios.');
        }

        if ($user->isRoot() && $request->user()->id !== $user->id) {
            return redirect()->route('users.index')->withErrors(['general' => 'Solo el propio usuario root puede modificar su cuenta.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if ($request->has('role') && $user->isRoot() && $data['role'] !== 'root') {
            return redirect()->route('users.index')->withErrors(['role' => 'No puedes cambiar el rol de un usuario root.']);
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($request->has('role')) {
            $user->syncRoles($data['role'] ?? []);
        }

        if ($request->has('verified') && $request->user()->hasAnyRole(['root', 'super-admin'])) {
            $user->email_verified_at = $request->boolean('verified') ? now() : null;
            $user->save();
        }

        ActivityLoggerService::log($request, 'user.updated', "Usuario \"{$user->name}\" actualizado");

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

        $userName = $user->name;
        $user->delete();

        ActivityLoggerService::log($request, 'user.deleted', "Usuario \"{$userName}\" eliminado");

        return redirect()->route('users.index');
    }
}
