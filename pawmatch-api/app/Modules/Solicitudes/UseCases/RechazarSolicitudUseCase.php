<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use App\Modules\Solicitudes\Repositories\HistorialRepository;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RechazarSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository,
        private HistorialRepository $historialRepository,
        private EmailService $emailService
    ) {}

    public function execute(int $solicitudId, string $motivoRechazo, int $adminId): array
    {
        $solicitud = $this->solicitudRepository->findById($solicitudId);

        if (!$solicitud) {
            throw new ModelNotFoundException('Solicitud no encontrada');
        }

        // Validar que la solicitud esté pendiente o en revisión
        if (!$solicitud->isPendiente() && $solicitud->estado !== 'EN_REVISION') {
            throw ValidationException::withMessages([
                'estado' => ['Solo se pueden rechazar solicitudes pendientes o en revisión.']
            ]);
        }

        $estadoAnterior = $solicitud->estado;

        // Cambiar estado a que este rechazada
        $solicitud->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => $motivoRechazo
        ]);

        // Registrar en historial
        $this->historialRepository->registrarCambio(
            solicitudId: $solicitud->id,
            estadoAnterior: $estadoAnterior,
            estadoActual: 'RECHAZADA',
            cambiadoPor: $adminId,
            comentario: "Rechazada: {$motivoRechazo}"
        );

        // Enviar notificación de rechazo
        $this->emailService->enviarSolicitudRechazada($solicitud);

        // Si no hay más solicitudes activas, marcar mascota como disponible
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
