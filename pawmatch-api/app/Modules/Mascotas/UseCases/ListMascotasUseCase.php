<?php

namespace App\Modules\Mascotas\UseCases;

use App\Modules\Mascotas\DTOs\FilterMascotasDTO;
use App\Modules\Mascotas\Repositories\MascotaRepository;

class ListMascotasUseCase
{
    public function __construct(
        private MascotaRepository $mascotaRepository
    ) {}

    public function execute(FilterMascotasDTO $filters): array
    {
        $mascotas = $this->mascotaRepository->list($filters);

        return [
            'data' => $mascotas->items(),
            'pagination' => [
                'total' => $mascotas->total(),
                'per_page' => $mascotas->perPage(),
                'current_page' => $mascotas->currentPage(),
                'last_page' => $mascotas->lastPage(),
                'from' => $mascotas->firstItem(),
                'to' => $mascotas->lastItem(),
            ]
        ];
    }
}