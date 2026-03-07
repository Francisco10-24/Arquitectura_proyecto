<?php

namespace App\Modules\Solicitudes\UseCases;

use App\Models\Mascota;
use App\Modules\Solicitudes\DTOs\CreateSolicitudDTO;
use App\Modules\Solicitudes\Repositories\SolicitudRepository;
use Illuminate\Validation\ValidationException;

class CreateSolicitudUseCase
{
    public function __construct(
        private SolicitudRepository $solicitudRepository
    ) {}

    public function execute(CreateSolicitudDTO $dto): array
    {
        $mascota = Mascota::find($dto->mascota_id);
        
        // Validación de mascota
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

        // Validación de existencia de solicitud
        $solicitudExistente = $this->solicitudRepository->findByUserAndMascota(
            $dto->user_id,
            $dto->mascota_id
        );

        if ($solicitudExistente) {
            throw ValidationException::withMessages([
                'mascota_id' => ['Ya tienes una solicitud activa para esta mascota.']
            ]);
        }

        // Crear solicitud

        $solicitud = $this->solicitudRepository->create($dto);

        $mascota->marcarComoEnProceso();

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
