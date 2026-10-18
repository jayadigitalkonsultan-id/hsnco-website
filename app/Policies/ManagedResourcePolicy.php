<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ManagedResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->managesContent();
    }

    public function view(User $user, Model $resource): bool
    {
        return $user->managesContent();
    }

    public function create(User $user): bool
    {
        return $user->managesContent();
    }

    public function update(User $user, Model $resource): bool
    {
        return $user->managesContent();
    }

    public function delete(User $user, Model $resource): bool
    {
        return $user->managesContent();
    }

    public function forceDelete(User $user, Model $resource): bool
    {
        return false;
    }
}
