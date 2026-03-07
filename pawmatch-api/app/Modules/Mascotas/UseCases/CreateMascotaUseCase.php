<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\DTOs\CreateMascotaDTO;
use App\Modules\Mascotas\Repositories\MascotaRepository;

class CreateMascotaUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(CreateMascotaDTO $dto): array
    {
        $mascota = $this->mascotaRepository->create($dto);

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
            'created_at' => $mascota->created_at->toISOString(),
        ];
    }
}