<?php

namespace App\Support;

use App\Events\SessionsRevoked;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Signs a user out of their devices: deletes their rows from the database `sessions`
 * table, rotates the remember-me token so "remember me" cookies stop working and tells
 * open pages (WebSockets) to reload, which sends them to the login page.
 */
class UserSessions
{
    public static function revoke(User $user, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->when($exceptSessionId, fn ($query) => $query->where('id', '!=', $exceptSessionId))
                ->delete();
        }

        $user->setRememberToken(Str::random(60));
        $user->saveQuietly();

        Realtime::broadcast(new SessionsRevoked($user->id, Realtime::sessionHash($exceptSessionId)));
    }
}
