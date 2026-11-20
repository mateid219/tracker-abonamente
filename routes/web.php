<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Authentication routes (/login, /register, /logout, /forgot-password ...)
// are registered by Laravel Fortify. See: php artisan route:list

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::delete('/settings/sessions', [SettingsController::class, 'destroyOtherSessions'])
        ->middleware('throttle:6,1')
        ->name('settings.sessions.destroy');

    // Admin area: user account management (administrators only)
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->except('show');
    });
});
