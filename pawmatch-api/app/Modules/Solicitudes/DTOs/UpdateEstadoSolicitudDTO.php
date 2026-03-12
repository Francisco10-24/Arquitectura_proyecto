<?php

namespace App\Modules\Solicitudes\DTOs;

class UpdateEstadoSolicitudDTO
{
    // Constructor de la clase
    public function __construct(
        public readonly string $estado,
        public readonly ?string $motivo_rechazo = null
    ) {}

    // Form request para actualizar el estado de una solicitud
    public static function fromRequest(array $data): self
    {
        return new self(
            estado: $data['estado'],
            motivo_rechazo: $data['motivo_rechazo'] ?? null
        );
    }
    // Devolver los datos en un array
    public function toArray(): array
    {
        $data = ['estado' => $this->estado];
        
        if ($this->motivo_rechazo) {
            $data['motivo_rechazo'] = $this->motivo_rechazo;
        }
        
        return $data;
    }
}