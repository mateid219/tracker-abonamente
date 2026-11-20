<?php

namespace App\Support;

/**
 * Sends a WebSocket (Reverb) event without ever breaking the request:
 * if the WebSocket server is down, the change is still saved and the error is only logged.
 *
 * Events are sent "to others": the browser tab that made the change (it sends its
 * X-Socket-ID header) does not receive its own event, the other tabs/windows do.
 */
class Realtime
{
    public static function broadcast(object $event): void
    {
        if (method_exists($event, 'dontBroadcastToCurrentUser')) {
            $event->dontBroadcastToCurrentUser();
        }

        rescue(fn () => event($event));
    }

    /**
     * Hash of a session id, used to recognise the current tab without exposing the id.
     */
    public static function sessionHash(?string $sessionId): ?string
    {
        return $sessionId ? hash_hmac('sha256', $sessionId, (string) config('app.key')) : null;
    }
}
