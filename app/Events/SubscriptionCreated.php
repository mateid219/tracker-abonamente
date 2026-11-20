<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/** A subscription was added: every dashboard of its owner adds it to the list. */
class SubscriptionCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @var array{id: int, name: string, monthly_cost: string, status: string} */
    public array $subscription;

    public int $userId;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription->toClientArray();
        $this->userId = $subscription->user_id;
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'subscription.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['subscription' => $this->subscription];
    }
}
