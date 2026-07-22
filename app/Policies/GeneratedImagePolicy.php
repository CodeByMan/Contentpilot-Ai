<?php

namespace App\Policies;

use App\Models\GeneratedImage;
use App\Models\User;

class GeneratedImagePolicy
{
    public function view(User $user, GeneratedImage $image): bool
    {
        return $image->user_id === $user->id;
    }

    public function delete(User $user, GeneratedImage $image): bool
    {
        return $image->user_id === $user->id;
    }
}
