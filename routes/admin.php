<?php

use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\OauthProviderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class)->except(['show', 'create', 'edit']);
    Route::resource('permissions', PermissionController::class)->except(['show', 'create', 'edit']);
    Route::get('api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
    Route::get('verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::patch('verification/{user}', [VerificationController::class, 'toggle'])->name('verification.toggle');
    Route::get('oauth-providers', [OauthProviderController::class, 'index'])->name('oauth-providers.index');
    Route::post('oauth-providers', [OauthProviderController::class, 'store'])->name('oauth-providers.store');
    Route::put('oauth-providers/{oauthProvider}', [OauthProviderController::class, 'update'])->name('oauth-providers.update');
    Route::delete('oauth-providers/{oauthProvider}', [OauthProviderController::class, 'destroy'])->name('oauth-providers.destroy');
    Route::patch('oauth-providers/{oauthProvider}/toggle', [OauthProviderController::class, 'toggle'])->name('oauth-providers.toggle');
    Route::patch('oauth-providers/{oauthProvider}/show-on-login', [OauthProviderController::class, 'toggleShowOnLogin'])->name('oauth-providers.show-on-login');
});
