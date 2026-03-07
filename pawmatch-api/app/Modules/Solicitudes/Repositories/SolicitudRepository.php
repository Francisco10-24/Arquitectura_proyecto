<?php

namespace App\Modules\Solicitudes\Repositories;

use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\DTOs\CreateSolicitudDTO;
use App\Modules\Solicitudes\DTOs\UpdateEstadoSolicitudDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class SolicitudRepository
{
    public function create(CreateSolicitudDTO $dto): SolicitudAdopcion
    {
        return SolicitudAdopcion::create($dto->toArray());
    }

    public function update(SolicitudAdopcion $solicitud, UpdateEstadoSolicitudDTO $dto): SolicitudAdopcion
    {
        $solicitud->update($dto->toArray());
        return $solicitud->fresh();
    }

    public function findById(int $id): ?SolicitudAdopcion
    {
        return SolicitudAdopcion::with(['user', 'mascota'])->find($id);
    }

    public function findByUserAndMascota(int $userId, int $mascotaId): ?SolicitudAdopcion
    {
        return SolicitudAdopcion::where('user_id', $userId)
            ->where('mascota_id', $mascotaId)
            ->whereIn('estado', ['PENDIENTE', 'EN_REVISION'])
            ->first();
    }

    public function listByUser(int $userId): LengthAwarePaginator
    {
        return SolicitudAdopcion::with(['mascota'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

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
