<?php

namespace App\Actions\Oauth;

use App\Models\OauthProvider;
use App\Services\OauthProviderService;

class UpdateProvider
{
    public function __construct(private OauthProviderService $providers) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(OauthProvider $provider, array $data): OauthProvider
    {
        return $this->providers->update($provider, $data);
    }
}
