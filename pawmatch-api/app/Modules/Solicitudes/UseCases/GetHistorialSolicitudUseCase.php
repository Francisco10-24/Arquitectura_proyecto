<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Modules\Solicitudes\Repositories\HistorialRepository;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetHistorialSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository,
        private HistorialRepository $historialRepository
    ) {}

    public function execute(int $solicitudId): array
    {
        // Verificar que la solicitud existe
        $solicitud = $this->solicitudRepository->findById($solicitudId);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        $historial = $this->historialRepository->obtenerHistorial($solicitudId);

        return [
            'solicitud_id' => $solicitudId,
            'historial' => $historial
        ];
    }
}
