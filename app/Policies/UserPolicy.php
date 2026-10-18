<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive() && $actor->role === UserRole::SuperAdmin;
    }

    public function view(User $actor, User $target): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->viewAny($actor);
    }

    public function delete(User $actor, User $target): bool
    {
        return false;
    }

    public function updatePreferences(User $actor, User $target): bool
    {
        return $actor->isActive() && $actor->is($target);
    }
}
