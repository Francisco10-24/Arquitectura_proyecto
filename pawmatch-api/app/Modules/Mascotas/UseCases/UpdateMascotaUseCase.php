<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\DTOs\UpdateMascotaDTO;
use App\Modules\Mascotas\Repositories\MascotaRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateMascotaUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(int $id, UpdateMascotaDTO $dto): array
    {
        $mascota = $this->mascotaRepository->findById($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota no encontrada');
        }

        $mascota = $this->mascotaRepository->update($mascota, $dto);

        return [
            'id' => $mascota->id,
            'nombre' => $mascota->nombre,
            'especie' => $mascota->especie,
            'raza' => $mascota->raza,
            'edad_aproximada' => $mascota->edad_aproximada,
            'sexo' => $mascota->sexo,
            'descripcion' => $mascota->descripcion,
            'foto_url' => $mascota->foto_url,
            'estado' => $mascota->estado,
            'updated_at' => $mascota->updated_at->toISOString(),
        ];
    }
}