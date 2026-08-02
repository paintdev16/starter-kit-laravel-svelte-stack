<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listFor(User $user): array
    {
        return $user->avatars()->latest()->get()->map(fn (Avatar $avatar): array => [
            'id' => $avatar->id,
            'url' => $avatar->url,
            'created_at' => $avatar->created_at,
        ])->all();
    }

    public function upload(User $user, UploadedFile $file): Avatar
    {
        $path = $file->store('avatars', 'public');

        return $user->avatars()->create(['path' => $path]);
    }

    public function delete(Avatar $avatar): void
    {
        if ($avatar->source === 'local') {
            Storage::disk('public')->delete($avatar->path);
        }

        $avatar->delete();
    }
}
