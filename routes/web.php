<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Authentication routes (/login, /register, /logout, /forgot-password ...)
// are registered by Laravel Fortify. See: php artisan route:list

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard: the user's subscriptions + total monthly cost
    Route::get('/dashboard', [SubscriptionController::class, 'index'])->name('dashboard');

    // Subscriptions CRUD
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::patch('/subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.status');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::delete('/settings/sessions', [SettingsController::class, 'destroyOtherSessions'])
        ->middleware('throttle:6,1')
        ->name('settings.sessions.destroy');

    // Admin area: user accounts CRUD with soft delete (administrators only)
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->except('show');
        Route::patch('users/{user}/restore', [AdminUserController::class, 'restore'])
            ->withTrashed()
            ->name('users.restore');
    });
});
