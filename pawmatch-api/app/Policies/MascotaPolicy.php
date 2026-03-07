<?php

namespace App\Policies;

use App\Models\Mascota;
use App\Models\User;

class MascotaPolicy
{
    /**
     * 
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * 
     */
    public function update(User $user, Mascota $mascota): bool
    {
        return $user->isAdmin();
    }

    /**
     * 
     */
    public function delete(User $user, Mascota $mascota): bool
    {
        return $user->isAdmin();
    }
}