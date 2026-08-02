<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tokens\CreateApiToken;
use App\Actions\Tokens\DeleteApiToken;
use App\Actions\Tokens\ListApiTokens;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApiTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function __construct(
        private readonly ListApiTokens $listApiTokens,
        private readonly CreateApiToken $createApiToken,
        private readonly DeleteApiToken $deleteApiToken,
    ) {}

    private function authorizeAccess(Request $request): void
    {
        if (! $request->user()->hasRole('root') && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Acceso denegado.');
        }
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        return response()->json(($this->listApiTokens)($request->user()));
    }

    public function store(StoreApiTokenRequest $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validated();
        $abilities = $data['abilities'] ?? ['*'];

        $token = ($this->createApiToken)($request->user(), $data['name'], $abilities);

        return response()->json([
            'id' => $token['accessToken']->id,
            'name' => $token['accessToken']->name,
            'abilities' => $token['accessToken']->abilities,
            'plain_text_token' => $token['plainTextToken'],
            'created_at' => $token['accessToken']->created_at,
        ], 201);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $this->authorizeAccess($request);

        ($this->deleteApiToken)($request->user(), $tokenId);

        return response()->json(null, 204);
    }
}
