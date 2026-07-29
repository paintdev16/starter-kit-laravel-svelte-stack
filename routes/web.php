<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\GlobalSearchController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified', 'inactivity:30'])->group(function () {
    Route::get(
        '/global-search',
        GlobalSearchController::class
    )
        ->middleware('auth')
        ->name('global.search');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('users', [\App\Http\Controllers\UsersController::class, 'index'])->name('users.index');
    Route::post('users', [\App\Http\Controllers\UsersController::class, 'store'])->name('users.store');
    Route::match(['put', 'patch'], 'users/{user}', [\App\Http\Controllers\UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [\App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');
    Route::get('activity/logs', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activity.logs');
    Route::get('activity/grouped', [\App\Http\Controllers\ActivityController::class, 'grouped'])->name('activity.grouped');
    Route::get('activity/current-device', [\App\Http\Controllers\ActivityController::class, 'currentDevice'])->name('activity.current-device');
    Route::get('users/activity', [\App\Http\Controllers\ActivityController::class, 'showActivity'])->name('users.activity');
    Route::get('avatars', [AvatarController::class, 'index'])->name('avatars.index');
    Route::post('avatars', [AvatarController::class, 'store'])->name('avatars.store');
    Route::delete('avatars/{avatar}', [AvatarController::class, 'destroy'])->name('avatars.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/admin.php';
