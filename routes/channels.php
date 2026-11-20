<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
| Private WebSocket channels. The browser asks /broadcasting/auth before listening;
| these callbacks decide who may listen.
*/

// Each user's own channel: their subscriptions and account changes, on all their open windows.
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return $user->id === $id;
});

// Changes to user accounts, for the admin area.
Broadcast::channel('admin.users', function (User $user) {
    return $user->isAdmin();
});
