<?php

namespace App\Http\Controllers;

use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialiteController extends Controller
{
    public function redirect(string $provider): SymfonyRedirectResponse|RedirectResponse
    {
        $oauth = $this->getEnabledProvider($provider);

        $this->configureSocialite($provider, $oauth);

        session()->put('socialite_provider', $provider);

        return Socialite::driver($provider)->redirect();
    }

    private function getEnabledProvider(string $provider): OauthProvider
    {
        return OauthProvider::query()
            ->where('provider', $provider)
            ->where('enabled', true)
            ->firstOr(fn () => abort(404, 'Proveedor no encontrado o deshabilitado.'));
    }

    private function configureSocialite(string $provider, OauthProvider $oauth): void
    {
        config()->set("services.{$provider}", [
            'client_id' => $oauth->client_id,
            'client_secret' => $oauth->client_secret,
            'redirect' => $oauth->redirect_uri,
        ]);
    }

    public function callback(Request $request): RedirectResponse
    {
        $provider = session()->pull('socialite_provider');

        if (! $provider) {
            return redirect()->route('login')->withErrors(['general' => 'Sesión de autenticación inválida.']);
        }

        $oauth = OauthProvider::where('provider', $provider)->where('enabled', true)->first();

        if (! $oauth) {
            return redirect()->route('login')->withErrors(['general' => 'Proveedor deshabilitado.']);
        }

        config()->set("services.{$provider}", [
            'client_id' => $oauth->client_id,
            'client_secret' => $oauth->client_secret,
            'redirect' => $oauth->redirect_uri,
        ]);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['general' => 'Error al autenticar con el proveedor.']);
        }

        if (! $socialUser->getEmail()) {
            return redirect()->route('login')->withErrors(['general' => 'El proveedor no proporcionó un correo electrónico.']);
        }

        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }
        }

        if (! $user) {
            $name = $socialUser->getName() ?? $socialUser->getNickname() ?? explode('@', $socialUser->getEmail())[0];

            $user = User::create([
                'name' => $name,
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'password' => null,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        $this->syncSocialAvatar($user, $socialUser->getAvatar());

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }

    private function syncSocialAvatar(User $user, ?string $avatarUrl): void
    {
        if (! $avatarUrl) {
            return;
        }

        $existingSocial = $user->avatars()->where('source', 'url')->first();

        if ($existingSocial) {
            $existingSocial->update(['path' => $avatarUrl]);

            return;
        }

        if ($user->avatars()->where('source', 'local')->exists()) {
            return;
        }

        $user->avatars()->create([
            'path' => $avatarUrl,
            'source' => 'url',
        ]);
    }
}
