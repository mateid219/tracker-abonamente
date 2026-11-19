<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Authentication routes (/login, /register, /logout, /forgot-password, 2FA ...)
// are registered by Laravel Fortify. See: php artisan route:list

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::get('/settings/two-factor/start', [SettingsController::class, 'startTwoFactor'])
        ->middleware('password.confirm')
        ->name('settings.two-factor.start');
    Route::delete('/settings/sessions', [SettingsController::class, 'destroyOtherSessions'])
        ->middleware('throttle:6,1')
        ->name('settings.sessions.destroy');
});
