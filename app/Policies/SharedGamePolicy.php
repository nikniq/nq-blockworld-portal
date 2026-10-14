<?php

namespace App\Policies;

use App\Models\SharedGame;
use App\Models\User;

class SharedGamePolicy
{
    public function update(User $user, SharedGame $game): bool
    {
        return $user->is_admin || $user->id === $game->user_id;
    }

    public function delete(User $user, SharedGame $game): bool
    {
        return $this->update($user, $game);
    }
}
