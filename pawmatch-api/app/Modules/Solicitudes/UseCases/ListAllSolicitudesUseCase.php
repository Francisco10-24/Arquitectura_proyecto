<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Modules\Solicitudes\Repositories\SolicitudRepository;

class ListAllSolicitudesUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository
    ) {}

    public function execute(?string $estado = null): array
    {
        // Implementar filtros
        $solicitudes = $this->solicitudRepository->listAll($estado);
        
        // Retornar datos
        $data = $solicitudes->map(function($solicitud) {
    return [
        'id' => $solicitud->id,
        'estado' => $solicitud->estado,
        'comentarios_adoptante' => $solicitud->comentarios_adoptante,
        'motivo_rechazo' => $solicitud->motivo_rechazo,
        'adoptante' => $solicitud->user ? [   // ✅ null-check aquí
            'id' => $solicitud->user->id,
            'nombre' => $solicitud->user->nombre,
            'email' => $solicitud->user->email,
            'telefono' => $solicitud->user->telefono,
        ] : null,
        'mascota' => $solicitud->mascota ? [   // ✅ null-check aquí
            'id' => $solicitud->mascota->id,
            'nombre' => $solicitud->mascota->nombre,
            'especie' => $solicitud->mascota->especie,
            'foto_url' => $solicitud->mascota->foto_url,
        ] : null,
        'created_at' => $solicitud->created_at->toISOString(),
        'updated_at' => $solicitud->updated_at->toISOString(),
    ];
});

        return [
            'data' => $data,
            'pagination' => [
                'total' => $solicitudes->total(),
                'per_page' => $solicitudes->perPage(),
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
            ]
        ];
    }
}