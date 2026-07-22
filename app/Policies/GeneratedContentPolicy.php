<?php

namespace App\Policies;

use App\Models\GeneratedContent;
use App\Models\User;

class GeneratedContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, GeneratedContent $document): bool
    {
        return $document->user_id === $user->id;
    }

    public function update(User $user, GeneratedContent $document): bool
    {
        return $document->user_id === $user->id;
    }

    public function delete(User $user, GeneratedContent $document): bool
    {
        return $document->user_id === $user->id;
    }
}
