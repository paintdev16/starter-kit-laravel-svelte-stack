<?php

namespace App\Actions\Oauth;

use App\Models\OauthProvider;
use App\Services\OauthProviderService;

class ToggleShowOnLogin
{
    public function __construct(private OauthProviderService $providers) {}

    public function __invoke(OauthProvider $provider): OauthProvider
    {
        return $this->providers->toggleShowOnLogin($provider);
    }
}
