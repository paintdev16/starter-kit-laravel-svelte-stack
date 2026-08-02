<?php

namespace App\Http\Controllers;

use App\Actions\Avatars\DeleteAvatar;
use App\Actions\Avatars\ListAvatars;
use App\Actions\Avatars\UploadAvatar;
use App\Http\Requests\Avatars\StoreAvatarRequest;
use App\Models\Avatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AvatarController extends Controller
{
    public function __construct(
        private readonly ListAvatars $listAvatars,
        private readonly UploadAvatar $uploadAvatar,
        private readonly DeleteAvatar $deleteAvatar,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(($this->listAvatars)($request->user()));
    }

    public function store(StoreAvatarRequest $request): RedirectResponse
    {
        ($this->uploadAvatar)($request->user(), $request->file('avatar'));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar agregado.']);

        return back();
    }

    public function destroy(Request $request, Avatar $avatar): RedirectResponse
    {
        if ($avatar->user_id !== $request->user()->id) {
            abort(403);
        }

        ($this->deleteAvatar)($avatar);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar eliminado.']);

        return back();
    }
}
