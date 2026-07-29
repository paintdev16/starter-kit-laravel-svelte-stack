<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->currentAvatarUrl(),
            'email_verified_at' => $user->email_verified_at,
            'has_two_factor' => $user->two_factor_confirmed_at !== null,
            'has_passkeys' => $user->passkeys()->exists(),
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return response()->json($data);
    }
}
