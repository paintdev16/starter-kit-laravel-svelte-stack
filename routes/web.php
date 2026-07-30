<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');

Route::middleware(['auth', 'verified', 'inactivity:30'])->group(function () {
    Route::get(
        '/global-search',
        GlobalSearchController::class
    )
        ->middleware('auth')
        ->name('global.search');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('users', [UsersController::class, 'index'])->name('users.index');
    Route::post('users', [UsersController::class, 'store'])->name('users.store');
    Route::match(['put', 'patch'], 'users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::get('activity/logs', [ActivityController::class, 'index'])->name('activity.logs');
    Route::get('activity/grouped', [ActivityController::class, 'grouped'])->name('activity.grouped');
    Route::get('activity/current-device', [ActivityController::class, 'currentDevice'])->name('activity.current-device');
    Route::get('users/activity', [ActivityController::class, 'showActivity'])->name('users.activity');
    Route::get('avatars', [AvatarController::class, 'index'])->name('avatars.index');
    Route::post('avatars', [AvatarController::class, 'store'])->name('avatars.store');
    Route::delete('avatars/{avatar}', [AvatarController::class, 'destroy'])->name('avatars.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
