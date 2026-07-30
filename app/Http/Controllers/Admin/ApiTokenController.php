<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        if (! $request->user()->hasRole('root') && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Acceso denegado.');
        }
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $tokens = $request->user()->tokens()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
            ]);

        return response()->json($tokens);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|exists:permissions,name',
        ]);

        $abilities = $data['abilities'] ?? ['*'];

        $token = $request->user()->createToken($data['name'], $abilities);

        return response()->json([
            'id' => $token->accessToken->id,
            'name' => $token->accessToken->name,
            'abilities' => $token->accessToken->abilities,
            'plain_text_token' => $token->plainTextToken,
            'created_at' => $token->accessToken->created_at,
        ], 201);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $this->authorizeAccess($request);

        $token = $request->user()->tokens()->findOrFail($tokenId);
        $token->delete();

        return response()->json(null, 204);
    }
}
