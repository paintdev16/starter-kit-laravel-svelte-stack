<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Settings\DeleteProfile;
use App\Actions\Settings\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Support\UserPresenter;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfile $updateProfile,
        private readonly DeleteProfile $deleteProfile,
    ) {}

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        ($this->updateProfile)($user, $request->validated());

        return response()->json([
            'message' => 'Perfil actualizado.',
            'user' => UserPresenter::toProfileArray($user),
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

        ($this->deleteProfile)($user);

        return response()->json(['message' => 'Cuenta eliminada.']);
    }
}
