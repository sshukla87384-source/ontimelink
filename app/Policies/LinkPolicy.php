<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Link $link): bool
    {
        return $link->user_id === $user->id;
    }

    public function disable(User $user, Link $link): bool
    {
        return $link->user_id === $user->id && $link->status === Link::STATUS_ACTIVE;
    }
}
