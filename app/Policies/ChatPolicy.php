<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    /**
     * Determine whether the user can view or interact with the chat.
     */
    public function view(User $user, Chat $chat): bool
    {
        return $user->isAdmin() || $user->id === $chat->user_id || $user->id === $chat->owner_id;
    }

    /**
     * Determine whether the user can send messages in the chat.
     */
    public function sendMessage(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id || $user->id === $chat->owner_id;
    }
}
