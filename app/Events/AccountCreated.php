<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/** An account was created or restored: open admin lists show a "new accounts" notice. */
class AccountCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @var array<string, mixed> */
    public array $user;

    public function __construct(User $user)
    {
        $this->user = AccountUpdated::payload($user);
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.users')];
    }

    public function broadcastAs(): string
    {
        return 'account.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['user' => $this->user];
    }
}
