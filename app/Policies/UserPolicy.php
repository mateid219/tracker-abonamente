<?php

namespace App\Policies;

use App\Models\User;

/**
 * Who may manage user accounts from the admin area.
 * Only administrators; nobody may delete their own account from there.
 */
class UserPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isAdmin() ? null : false;
    }

    public function viewAny(User $actor): bool
    {
        return true;
    }

    public function create(User $actor): bool
    {
        return true;
    }

    public function update(User $actor, User $target): bool
    {
        return true;
    }

    public function delete(User $actor, User $target): bool
    {
        return ! $actor->is($target);
    }

    public function restore(User $actor, User $target): bool
    {
        return $target->trashed();
    }
}
