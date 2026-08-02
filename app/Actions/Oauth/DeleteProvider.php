<?php

namespace App\Actions\Oauth;

use App\Models\OauthProvider;
use App\Services\OauthProviderService;

class DeleteProvider
{
    public function __construct(private OauthProviderService $providers) {}

    public function __invoke(OauthProvider $provider): void
    {
        $this->providers->delete($provider);
    }
}
