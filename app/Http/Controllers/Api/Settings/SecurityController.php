<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;

class SecurityController extends Controller
{
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => $request->password,
        ]);

        ActivityLoggerService::log($request, 'auth.password_changed', "Contraseña cambiada: \"{$user->name}\"");

        return response()->json(['message' => 'Contraseña actualizada.']);
    }
}
