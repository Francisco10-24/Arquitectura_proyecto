<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Models\SolicitudAdopcion;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use App\Modules\Solicitudes\Repositories\HistorialRepository;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class AprobarSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository,
        private HistorialRepository $historialRepository,
        private EmailService $emailService
    ) {}

    public function execute(int $solicitudId, int $adminId): array
    {
        $solicitud = $this->solicitudRepository->findById($solicitudId);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        // Validar que la solicitud esté pendiente
        if (!$solicitud->isPendiente() && $solicitud->estado !== 'EN_REVISION') {
            throw ValidationException::withMessages([
                'estado' => ['Solo se pueden aprobar solicitudes pendientes o en revisión.']
            ]);
        }

        // Validar que la mascota siga disponible o en proceso
        if (!in_array($solicitud->mascota->estado, ['DISPONIBLE', 'EN_PROCESO'])) {
            throw ValidationException::withMessages([
                'mascota' => ['La mascota ya no está disponible.']
            ]);
        }

        $estadoAnterior = $solicitud->estado;

        // Cambiar estado de solicitud a que este aprobada
        $solicitud->update(['estado' => 'APROBADA']);

        // Registrar en historial
        $this->historialRepository->registrarCambio(
            solicitudId: $solicitud->id,
            estadoAnterior: $estadoAnterior,
            estadoActual: 'APROBADA',
            cambiadoPor: $adminId,
            comentario: 'Solicitud aprobada por administrador'
        );

        // Cambiar estado de mascota a que este adoptada
        $solicitud->mascota->marcarComoAdoptada();

        // Enviar notificación de aprobación
        $this->emailService->enviarSolicitudAprobada($solicitud);

        // Rechazar automáticamente otras solicitudes pendientes
        $otrasSolicitudes = SolicitudAdopcion::where('mascota_id', $solicitud->mascota_id)
            ->where('id', '!=', $solicitud->id)
            ->whereIn('estado', ['PENDIENTE', 'EN_REVISION'])
            ->get();

        foreach ($otrasSolicitudes as $otraSolicitud) {
            $estadoAnteriorOtra = $otraSolicitud->estado;

            $otraSolicitud->update([
                'estado' => 'RECHAZADA',
                'motivo_rechazo' => 'La mascota ya fue adoptada por otro solicitante.'
            ]);

            // Registrar cambio en historial
            $this->historialRepository->registrarCambio(
                solicitudId: $otraSolicitud->id,
                estadoAnterior: $estadoAnteriorOtra,
                estadoActual: 'RECHAZADA',
                cambiadoPor: $adminId,
                comentario: 'Rechazada automáticamente - mascota adoptada por otro solicitante'
            );

            // Enviar notificación de rechazo
            $this->emailService->enviarSolicitudRechazada($otraSolicitud);
        }

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
