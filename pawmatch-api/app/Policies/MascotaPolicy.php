<?php

namespace App\Policies;

use App\Models\Mascota;
use App\Models\User;

class MascotaPolicy
{
    /**
     * Administradores pueden crear mascotas
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administradores pueden actualizar mascotas
     */
    public function update(User $user, Mascota $mascota): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administradores pueden eliminar mascotas
     */
    public function delete(User $user, Mascota $mascota): bool
    {
        return $user->isAdmin();
    }
}
