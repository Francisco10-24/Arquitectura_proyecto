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
        $mascotas = $this->mascotaRepository->getTrashed();

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
