<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    public function destroy(ProfileDeleteRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isRoot()) {
            $rootCount = User::role('root')->count();
            if ($rootCount <= 1) {
                return response()->json(['message' => 'No puedes eliminar la cuenta del único usuario root.'], 403);
            }
        }

        $user->delete();

        return response()->json(['message' => 'Cuenta eliminada.']);
    }
}
