<?php

namespace App\Policies;

use App\Models\PageSection;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PageSectionPolicy
{
    public function view(User $user, PageSection $section): bool
    {
        return $section->page !== null && Gate::forUser($user)->allows('view', $section->page);
    }

    public function update(User $user, PageSection $section): bool
    {
        return $section->page !== null && Gate::forUser($user)->allows('update', $section->page);
    }

    public function delete(User $user, PageSection $section): bool
    {
        return $this->update($user, $section);
    }
}
