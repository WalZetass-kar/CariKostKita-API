<?php

namespace App\Policies;

use App\Models\Kost;
use App\Models\User;

class KostPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kost $kost): bool
    {
        return $user->isAdmin() || ($user->isPemilik() && $kost->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kost $kost): bool
    {
        return $user->isAdmin() || ($user->isPemilik() && $kost->owner_id === $user->id);
    }

    /**
     * Determine whether the user can manage rooms/images of the kost.
     */
    public function manage(User $user, Kost $kost): bool
    {
        return $user->isAdmin() || ($user->isPemilik() && $kost->owner_id === $user->id);
    }
}
