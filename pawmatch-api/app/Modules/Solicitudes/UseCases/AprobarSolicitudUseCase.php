<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class AprobarSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository
    ) {}

    public function execute(int $solicitudId): array
    {
        $solicitud = $this->solicitudRepository->findById($solicitudId);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        // Validaciones
        if (!$solicitud->isPendiente() && $solicitud->estado !== 'EN_REVISION') {
            throw ValidationException::withMessages([
                'estado' => ['Solo se pueden aprobar solicitudes pendientes o en revisión.']
            ]);
        }

        if (!in_array($solicitud->mascota->estado, ['DISPONIBLE', 'EN_PROCESO'])) {
            throw ValidationException::withMessages([
                'mascota' => ['La mascota ya no está disponible.']
            ]);
        }

        $solicitud->update(['estado' => 'APROBADA']);

        // Cambiar estado de mascota a adoptada
        $solicitud->mascota->marcarComoAdoptada();



        // Cambiar estado de otras solicitudes para avisar que ya se ha adoptado a la mascota
        SolicitudAdopcion::where('mascota_id', $solicitud->mascota_id)
            ->where('id', '!=', $solicitud->id)
            ->whereIn('estado', ['PENDIENTE', 'EN_REVISION'])
            ->update([
                'estado' => 'RECHAZADA',
                'motivo_rechazo' => 'La mascota ya fue adoptada por otro solicitante.'
            ]);

        // Retornar datos

        return [
            'id' => $solicitud->id,
            'estado' => $solicitud->estado,
            'mascota' => [
                'id' => $solicitud->mascota->id,
                'nombre' => $solicitud->mascota->nombre,
                'estado' => $solicitud->mascota->estado,
            ],
            'updated_at' => $solicitud->updated_at->toISOString(),
        ];
    }
}
