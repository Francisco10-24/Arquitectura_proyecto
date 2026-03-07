<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Modules\Solicitudes\DTOs\UpdateEstadoSolicitudDTO;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RechazarSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository
    ) {}

    public function execute(int $solicitudId, string $motivoRechazo): array
    {
        $solicitud = $this->solicitudRepository->findById($solicitudId);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        // Validar que el estado de la solicitud y que sea pendiente o en revisión
        if (!$solicitud->isPendiente() && $solicitud->estado !== 'EN_REVISION') {
            throw ValidationException::withMessages([
                'estado' => ['Solo se pueden rechazar solicitudes pendientes o en revisión.']
            ]);
        }

        // Rechazar solicitud
        $solicitud->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => $motivoRechazo
        ]);

        // Marcar mascota como disponible si no tiene mas solicitudes activas
        $solicitudesActivas = $solicitud->mascota->solicitudes()
            ->whereIn('estado', ['PENDIENTE', 'EN_REVISION'])
            ->count();

        if ($solicitudesActivas === 0) {
            $solicitud->mascota->update(['estado' => 'DISPONIBLE']);
        }

        return [
            'id' => $solicitud->id,
            'estado' => $solicitud->estado,
            'motivo_rechazo' => $solicitud->motivo_rechazo,
            'updated_at' => $solicitud->updated_at->toISOString(),
        ];
    }
}
