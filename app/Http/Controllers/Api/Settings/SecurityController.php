<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Settings\UpdatePassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\JsonResponse;

class SecurityController extends Controller
{
    public function __construct(
        private readonly UpdatePassword $updatePassword,
    ) {}

    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        ($this->updatePassword)($request, $request->user(), $request->password);

        return response()->json(['message' => 'Contraseña actualizada.']);
    }
}
