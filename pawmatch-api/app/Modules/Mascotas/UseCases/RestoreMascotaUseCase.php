<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\Repositories\MascotaRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestoreMascotaUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(int $id): array
    {
        $mascota = $this->mascotaRepository->restore($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota eliminada no encontrada');
        }

        return [
            'id' => $mascota->id,
            'nombre' => $mascota->nombre,
            'especie' => $mascota->especie,
            'estado' => $mascota->estado,
        ];
    }
}