<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * The user's sessions were revoked: open pages reload and land on the login page.
 * The page whose session was kept (if any) ignores it, recognised by a hash of its session id.
 */
class SessionsRevoked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $userId, public ?string $exceptSessionHash = null) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'sessions.revoked';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['except_session' => $this->exceptSessionHash];
    }
}
