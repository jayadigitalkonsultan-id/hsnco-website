<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Media $media): bool
    {
        return $user->isActive() && ($media->visibility === 'public' || $this->update($user, $media));
    }

    public function update(User $user, Media $media): bool
    {
        return $user->isActive() && ($user->managesContent() || $media->uploaded_by === $user->id);
    }

    public function delete(User $user, Media $media): bool
    {
        return $media->disk !== 'frontend' && $this->update($user, $media);
    }

    public function forceDelete(User $user, Media $media): bool
    {
        return false;
    }
}
