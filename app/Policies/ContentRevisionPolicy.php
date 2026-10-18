<?php

namespace App\Policies;

use App\Enums\ContentStatus;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ContentRevisionPolicy
{
    public function view(User $user, ContentRevision $revision): bool
    {
        return $revision->revisionable !== null && Gate::forUser($user)->allows('view', $revision->revisionable);
    }

    public function update(User $user, ContentRevision $revision): bool
    {
        return $revision->status !== ContentStatus::Published && $revision->revisionable !== null
            && Gate::forUser($user)->allows('revise', $revision->revisionable);
    }

    public function publish(User $user, ContentRevision $revision): bool
    {
        return $revision->status === ContentStatus::Review && $revision->revisionable !== null
            && Gate::forUser($user)->allows('publish', $revision->revisionable);
    }

    public function delete(User $user, ContentRevision $revision): bool
    {
        return false;
    }
}
