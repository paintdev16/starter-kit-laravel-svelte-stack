<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $avatars = $request->user()->avatars()->latest()->get()->map(fn (Avatar $a): array => [
            'id' => $a->id,
            'url' => $a->url,
            'created_at' => $a->created_at,
        ]);

        return response()->json($avatars);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $avatar = $request->user()->avatars()->create(['path' => $path]);

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

        if ($avatar->source === 'local') {
            Storage::disk('public')->delete($avatar->path);
        }

        $avatar->delete();

        return response()->json(['message' => 'Avatar eliminado.']);
    }
}
