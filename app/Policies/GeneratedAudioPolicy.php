<?php

namespace App\Policies;

use App\Models\GeneratedAudio;
use App\Models\User;

class GeneratedAudioPolicy
{
    public function view(User $user, GeneratedAudio $audio): bool
    {
        return $audio->user_id === $user->id;
    }

    public function delete(User $user, GeneratedAudio $audio): bool
    {
        return $audio->user_id === $user->id;
    }
}
