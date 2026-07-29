<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\GlobalSearchController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\Api\Settings\SecurityController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('users', [UsersController::class, 'index']);
    Route::post('users', [UsersController::class, 'store']);
    Route::get('users/{user}', [UsersController::class, 'show']);
    Route::match(['put', 'patch'], 'users/{user}', [UsersController::class, 'update']);
    Route::delete('users/{user}', [UsersController::class, 'destroy']);

    Route::get('activity/logs', [ActivityController::class, 'index']);
    Route::get('activity/grouped', [ActivityController::class, 'grouped']);
    Route::get('activity/current-device', [ActivityController::class, 'currentDevice']);

    Route::get('global-search', [GlobalSearchController::class, '__invoke']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('settings/profile', [SettingsProfileController::class, 'update']);
    Route::delete('settings/profile', [SettingsProfileController::class, 'destroy']);
    Route::put('settings/password', [SecurityController::class, 'update']);

    Route::get('avatars', [AvatarController::class, 'index']);
    Route::post('avatars', [AvatarController::class, 'store']);
    Route::delete('avatars/{avatar}', [AvatarController::class, 'destroy']);

    Route::apiResource('admin/roles', RoleController::class)->except(['show']);
    Route::apiResource('admin/permissions', PermissionController::class)->except(['show']);
});
