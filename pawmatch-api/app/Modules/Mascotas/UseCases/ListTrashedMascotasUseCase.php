<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\Repositories\MascotaRepository;

class ListTrashedMascotasUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(): array
    {
        //Obtener mascotas eliminadas
        $mascotas = $this->mascotaRepository->getTrashed();

        // Retornar los resultados que se encontraron
        return [
            'data' => $mascotas->items(),
            'pagination' => [
                'total' => $mascotas->total(),
                'per_page' => $mascotas->perPage(),
                'current_page' => $mascotas->currentPage(),
                'last_page' => $mascotas->lastPage(),
            ]
        ];
    }
}
