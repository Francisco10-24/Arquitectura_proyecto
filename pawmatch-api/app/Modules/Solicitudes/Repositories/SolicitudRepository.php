<?php

namespace App\Modules\Solicitudes\Repositories;

use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\DTOs\CreateSolicitudDTO;
use App\Modules\Solicitudes\DTOs\UpdateEstadoSolicitudDTO;
use Illuminate\Pagination\LengthAwarePaginator;

// Clase para manejar las solicitudes de adopción
class SolicitudRepository
{
    // Crear una nueva solicitud de adopción
    public function create(CreateSolicitudDTO $dto): SolicitudAdopcion
    {
        return SolicitudAdopcion::create($dto->toArray());
    }
    // Actualizar la solicitud de adopción
    public function update(SolicitudAdopcion $solicitud, UpdateEstadoSolicitudDTO $dto): SolicitudAdopcion
    {
        $solicitud->update($dto->toArray());
        return $solicitud->fresh();
    }

    // Encontrar una solicitud de adopción por su ID
    public function findById(int $id): ?SolicitudAdopcion
    {
        return SolicitudAdopcion::with(['user', 'mascota'])->find($id);
    }

    // Encontrar una solicitud por id de usuario y mascota
    public function findByUserAndMascota(int $userId, int $mascotaId): ?SolicitudAdopcion
    {
        return SolicitudAdopcion::where('user_id', $userId)
            ->where('mascota_id', $mascotaId)
            ->whereIn('estado', ['PENDIENTE', 'EN_REVISION'])
            ->first();
    }

    // Listar las solicitudes de un usuario 
    public function listByUser(int $userId): LengthAwarePaginator
    {
        return SolicitudAdopcion::with(['mascota'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    // Listar todas las solicitudes
    public function listAll(?string $estado = null): LengthAwarePaginator
    {
        $query = SolicitudAdopcion::with(['user', 'mascota']);

        if ($estado) {
            $query->where('estado', $estado);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
