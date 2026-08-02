<?php

namespace App\Actions\Avatars;

use App\Models\Avatar;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\UploadedFile;

class UploadAvatar
{
    public function __construct(private AvatarService $avatars) {}

    public function __invoke(User $user, UploadedFile $file): Avatar
    {
        return $this->avatars->upload($user, $file);
    }
}
