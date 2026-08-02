<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Oauth\DeleteProvider;
use App\Actions\Oauth\StoreProvider;
use App\Actions\Oauth\ToggleProvider;
use App\Actions\Oauth\ToggleShowOnLogin;
use App\Actions\Oauth\UpdateProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOauthProviderRequest;
use App\Http\Requests\Admin\UpdateOauthProviderRequest;
use App\Models\OauthProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OauthProviderController extends Controller
{
    public function __construct(
        private readonly StoreProvider $storeProvider,
        private readonly UpdateProvider $updateProvider,
        private readonly DeleteProvider $deleteProvider,
        private readonly ToggleProvider $toggleProvider,
        private readonly ToggleShowOnLogin $toggleShowOnLogin,
    ) {}

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasRole('root'), 403, 'No tienes permiso para gestionar proveedores OAuth.');
    }

    /**
     * @return array<string, mixed>
     */
    private function providerPayload(OauthProvider $provider): array
    {
        return [
            'id' => $provider->id,
            'provider' => $provider->provider,
            'client_id' => $provider->client_id,
            'redirect_uri' => $provider->redirect_uri,
            'enabled' => $provider->enabled,
            'show_on_login' => $provider->show_on_login,
            'sort' => $provider->sort,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $providers = OauthProvider::orderBy('sort')->orderBy('provider')->get();

        return response()->json($providers->map(fn (OauthProvider $provider): array => [
            ...$this->providerPayload($provider),
            'created_at' => $provider->created_at,
        ]));
    }

    public function store(StoreOauthProviderRequest $request): JsonResponse
    {
        $this->authorizeAccess($request);

        $provider = ($this->storeProvider)($request->validated());

        return response()->json([
            ...$this->providerPayload($provider),
            'created_at' => $provider->created_at,
        ], 201);
    }

    public function update(UpdateOauthProviderRequest $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $provider = ($this->updateProvider)($oauthProvider, $request->validated());

        return response()->json($this->providerPayload($provider));
    }

    public function destroy(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        ($this->deleteProvider)($oauthProvider);

        return response()->json(null, 204);
    }

    public function toggleShowOnLogin(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $provider = ($this->toggleShowOnLogin)($oauthProvider);

        return response()->json([
            'id' => $provider->id,
            'show_on_login' => $provider->show_on_login,
        ]);
    }

    public function toggle(Request $request, OauthProvider $oauthProvider): JsonResponse
    {
        $this->authorizeAccess($request);

        $provider = ($this->toggleProvider)($oauthProvider);

        return response()->json([
            'id' => $provider->id,
            'enabled' => $provider->enabled,
        ]);
    }
}
