<?php

namespace App\Http\Controllers\Api;

use App\Actions\Avatars\DeleteAvatar;
use App\Actions\Avatars\ListAvatars;
use App\Actions\Avatars\UploadAvatar;
use App\Http\Controllers\Controller;
use App\Http\Requests\Avatars\StoreAvatarRequest;
use App\Models\Avatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(StoreAvatarRequest $request): JsonResponse
    {
        $avatar = ($this->uploadAvatar)($request->user(), $request->file('avatar'));

        return response()->json([
            'message' => 'Avatar agregado.',
            'avatar' => [
                'id' => $avatar->id,
                'url' => $avatar->url,
                'created_at' => $avatar->created_at,
            ],
        ], 201);
    }

    public function destroy(Request $request, Avatar $avatar): JsonResponse
    {
        if ($avatar->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        ($this->deleteAvatar)($avatar);

        return response()->json(['message' => 'Avatar eliminado.']);
    }
}
