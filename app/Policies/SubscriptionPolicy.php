<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/**
 * Each user manages only their own subscriptions.
 */
class SubscriptionPolicy
{
    public function update(User $user, Subscription $subscription): bool
    {
        return $subscription->user_id === $user->id;
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $subscription->user_id === $user->id;
    }
}
