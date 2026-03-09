<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Models\Mascota;
use App\Modules\Solicitudes\DTOs\CreateSolicitudDTO;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use App\Modules\Solicitudes\Repositories\HistorialRepository;
use App\Services\EmailService;
use Illuminate\Validation\ValidationException;

class CreateSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository,
        private HistorialRepository $historialRepository,
        private EmailService $emailService
    ) {}

    public function execute(CreateSolicitudDTO $dto): array
    {
        // Validar que la mascota existe y está disponible
        $mascota = Mascota::find($dto->mascota_id);

        if (!$mascota) {
            throw ValidationException::withMessages([
                'mascota_id' => ['La mascota no existe.']
            ]);
        }

        if (!$mascota->isDisponible()) {
            throw ValidationException::withMessages([
                'mascota_id' => ['La mascota no está disponible para adopción.']
            ]);
        }

        // Validar que no exista una solicitud activa
        $solicitudExistente = $this->solicitudRepository->findByUserAndMascota(
            $dto->user_id,
            $dto->mascota_id
        );

        if ($solicitudExistente) {
            throw ValidationException::withMessages([
                'mascota_id' => ['Ya tienes una solicitud activa para esta mascota.']
            ]);
        }

        // Crear la solicitud
        $solicitud = $this->solicitudRepository->create($dto);

        // Registrar en historial
        $this->historialRepository->registrarCambio(
            solicitudId: $solicitud->id,
            estadoAnterior: null,
            estadoActual: 'PENDIENTE',
            cambiadoPor: $dto->user_id,
            comentario: 'Solicitud creada'
        );

        // Cambiar estado de mascota a que este en proceso
        $mascota->marcarComoEnProceso();

        // Enviar notificación por correo
        $this->emailService->enviarSolicitudCreada($solicitud);

        return [
            'id' => $solicitud->id,
            'user_id' => $solicitud->user_id,
            'mascota_id' => $solicitud->mascota_id,
            'estado' => $solicitud->estado,
            'comentarios_adoptante' => $solicitud->comentarios_adoptante,
            'created_at' => $solicitud->created_at->toISOString(),
        ];
    }
}
