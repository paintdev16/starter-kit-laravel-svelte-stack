<?php

namespace App\Services;

use App\Models\OauthProvider;

class OauthProviderService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): OauthProvider
    {
        return OauthProvider::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(OauthProvider $provider, array $data): OauthProvider
    {
        if (empty($data['client_secret'])) {
            unset($data['client_secret']);
        }

        $provider->update($data);

        return $provider;
    }

    public function delete(OauthProvider $provider): void
    {
        $provider->delete();
    }

    public function toggleEnabled(OauthProvider $provider): OauthProvider
    {
        $provider->update([
            'enabled' => ! $provider->enabled,
        ]);

        return $provider;
    }

    public function toggleShowOnLogin(OauthProvider $provider): OauthProvider
    {
        $provider->update([
            'show_on_login' => ! $provider->show_on_login,
        ]);

        return $provider;
    }
}
