<?php

namespace App\Http\Controllers;

use App\Models\Avatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $request->user()->avatars()->create(['path' => $path]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar agregado.']);

        return back();
    }

    public function destroy(Request $request, Avatar $avatar): RedirectResponse
    {
        if ($avatar->user_id !== $request->user()->id) {
            abort(403);
        }

        Storage::disk('public')->delete($avatar->path);
        $avatar->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Avatar eliminado.']);

        return back();
    }
}
