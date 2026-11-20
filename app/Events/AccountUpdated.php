<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A user's account changed (name, email, role...).
 * Sent to the user's own pages (name in the menu/dashboard) and to admins (users list).
 */
class AccountUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @var array<string, mixed> */
    public array $user;

    public function __construct(User $user)
    {
        $this->user = self::payload($user);
    }

    /** @return array<string, mixed> */
    public static function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'is_admin' => $user->isAdmin(),
            'verified' => $user->hasVerifiedEmail(),
        ];
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->user['id']),
            new PrivateChannel('admin.users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'account.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['user' => $this->user];
    }
}
