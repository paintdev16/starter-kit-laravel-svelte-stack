<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Support\PasskeyPresenter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? PasskeyPresenter::collection($request->user())
                : [],
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return Inertia::render('profile/Index', $props);
    }
}
