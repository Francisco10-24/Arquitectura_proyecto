<?php

namespace App\Policies;

use App\Models\SolicitudAdopcion;
use App\Models\User;

class SolicitudAdopcionPolicy
{
    /**
     * Determinar si el usuario puede ver la solicitud
     */
    public function view(User $user, SolicitudAdopcion $solicitud): bool
    {
        // El administrador podrá ver todas, el usuario solo las suyas
        return $user->isAdmin() || $solicitud->user_id === $user->id;
    }

    /**
     * Usuarios autenticados pueden crear solicitudes
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Usuario autenticado como administrador puede actualizar estado de solicitud
     */
    public function updateEstado(User $user): bool
    {
        return $user->isAdmin();
    }
}