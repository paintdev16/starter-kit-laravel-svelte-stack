<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OauthProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OauthProviderController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasRole('root'), 403, 'No tienes permiso para gestionar proveedores OAuth.');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $providers = OauthProvider::orderBy('sort')->orderBy('provider')->get()->map(fn ($p) => [
            'id' => $p->id,
            'provider' => $p->provider,
            'client_id' => $p->client_id,
            'redirect_uri' => $p->redirect_uri,
            'enabled' => $p->enabled,
            'show_on_login' => $p->show_on_login,
            'sort' => $p->sort,
            'created_at' => $p->created_at,
        ]);

        return response()->json($providers);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'provider' => 'required|string|max:100|unique:oauth_providers,provider',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|url',
            'enabled' => 'boolean',
            'show_on_login' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        $provider = OauthProvider::create($data);

        return response()->json([
            'id' => $provider->id,
            'provider' => $provider->provider,
            'client_id' => $provider->client_id,
            'redirect_uri' => $provider->redirect_uri,
            'enabled' => $provider->enabled,
            'show_on_login' => $provider->show_on_login,
            'sort' => $provider->sort,
            'created_at' => $provider->created_at,
        ], 201);
    }

    public function update(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'provider' => 'required|string|max:100|unique:oauth_providers,provider,'.$oauthProvider->id,
            'client_id' => 'required|string',
            'client_secret' => 'nullable|string',
            'redirect_uri' => 'required|url',
            'enabled' => 'boolean',
            'show_on_login' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        if (empty($data['client_secret'])) {
            unset($data['client_secret']);
        }

        $oauthProvider->update($data);

        return response()->json([
            'id' => $oauthProvider->id,
            'provider' => $oauthProvider->provider,
            'client_id' => $oauthProvider->client_id,
            'redirect_uri' => $oauthProvider->redirect_uri,
            'enabled' => $oauthProvider->enabled,
            'show_on_login' => $oauthProvider->show_on_login,
            'sort' => $oauthProvider->sort,
        ]);
    }

    public function destroy(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $oauthProvider->delete();

        return response()->json(null, 204);
    }

    public function toggleShowOnLogin(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $oauthProvider->update([
            'show_on_login' => ! $oauthProvider->show_on_login,
        ]);

        return response()->json([
            'id' => $oauthProvider->id,
            'show_on_login' => $oauthProvider->show_on_login,
        ]);
    }

    public function toggle(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $oauthProvider->update([
            'enabled' => ! $oauthProvider->enabled,
        ]);

        return response()->json([
            'id' => $oauthProvider->id,
            'enabled' => $oauthProvider->enabled,
        ]);
    }
}
