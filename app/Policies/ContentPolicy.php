<?php

namespace App\Policies;

use App\Enums\ContentStatus;
use App\Models\ContentModel;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, ContentModel $content): bool
    {
        return $user->isActive() && ($user->managesContent() || $content->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function revise(User $user, ContentModel $content): bool
    {
        return $this->view($user, $content) && ! $content->trashed();
    }

    public function update(User $user, ContentModel $content): bool
    {
        // DraftContentService writes a new immutable revision for live content.
        return $this->revise($user, $content)
            && $content->status !== ContentStatus::Archived;
    }

    public function publish(User $user, ContentModel $content): bool
    {
        return $user->managesContent() && ! $content->trashed()
            && in_array($content->status, [ContentStatus::Review, ContentStatus::Published], true);
    }

    public function archive(User $user, ContentModel $content): bool
    {
        return $user->managesContent() && ! $content->trashed() && $content->status === ContentStatus::Published;
    }

    public function delete(User $user, ContentModel $content): bool
    {
        return $this->update($user, $content)
            && ! in_array($content->status, [ContentStatus::Published, ContentStatus::Archived], true);
    }

    public function restore(User $user, ContentModel $content): bool
    {
        return $user->managesContent() && $content->trashed();
    }

    public function forceDelete(User $user, ContentModel $content): bool
    {
        return false;
    }
}
