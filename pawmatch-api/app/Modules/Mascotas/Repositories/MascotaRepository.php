<?php

namespace App\Modules\Mascotas\Repositories;

use App\Models\Mascota;
use App\Modules\Mascotas\DTOs\CreateMascotaDTO;
use App\Modules\Mascotas\DTOs\UpdateMascotaDTO;
use App\Modules\Mascotas\DTOs\FilterMascotasDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class MascotaRepository
{
    // Métodos para la base de datos
    public function create(CreateMascotaDTO $dto): Mascota
    {
        return Mascota::create($dto->toArray());
    }

    public function update(Mascota $mascota, UpdateMascotaDTO $dto): Mascota
    {
        $mascota->update($dto->toArray());
        return $mascota->fresh();
    }

    public function delete(Mascota $mascota): bool
    {
        return $mascota->delete();
    }

    public function findById(int $id): ?Mascota
    {
        return Mascota::find($id);
    }

    public function list(FilterMascotasDTO $filters): LengthAwarePaginator
    {
        $query = Mascota::query();

        // Filtro por especie
        if ($filters->especie) {
            $query->where('especie', $filters->especie);
        }

        // Filtro por estado
        if ($filters->estado) {
            $query->where('estado', $filters->estado);
        } else {

            $query->where('estado', 'DISPONIBLE');
        }

        // Filtro por sexo
        if ($filters->sexo) {
            $query->where('sexo', $filters->sexo);
        }

        // Búsqueda por nombre o descripción
        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('nombre', 'like', "%{$filters->search}%")
                    ->orWhere('descripcion', 'like', "%{$filters->search}%")
                    ->orWhere('raza', 'like', "%{$filters->search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters->per_page);
    }

    // Métodos para administradores
    public function getAllForAdmin(): LengthAwarePaginator
    {
        return Mascota::orderBy('created_at', 'desc')->paginate(15);
    }

    public function getTrashed(): LengthAwarePaginator
    {
        return Mascota::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);
    }

    // Métodos para el soft delete

    public function restore(int $id): ?Mascota
    {
        $mascota = Mascota::onlyTrashed()->find($id);

        if ($mascota) {
            $mascota->restore();
            return $mascota;
        }

        return null;
    }

    public function forceDelete(int $id): bool
    {
        $mascota = Mascota::onlyTrashed()->find($id);

        if ($mascota) {
            return $mascota->forceDelete();
        }

        return false;
    }
}
