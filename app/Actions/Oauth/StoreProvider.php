<?php

namespace App\Actions\Oauth;

use App\Models\OauthProvider;
use App\Services\OauthProviderService;

class StoreProvider
{
    public function __construct(private OauthProviderService $providers) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): OauthProvider
    {
        return $this->providers->create($data);
    }
}
