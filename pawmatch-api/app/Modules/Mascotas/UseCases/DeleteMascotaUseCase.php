<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\Repositories\MascotaRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteMascotaUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(int $id): void
    {
        // Encontrar la mascota
        $mascota = $this->mascotaRepository->findById($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota no encontrada');
        }
        // Eliminar la mascota
        $this->mascotaRepository->delete($mascota);
    }
}