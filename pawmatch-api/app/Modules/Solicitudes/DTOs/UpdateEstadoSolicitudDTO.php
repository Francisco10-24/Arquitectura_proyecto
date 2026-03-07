<?php

namespace App\Modules\Solicitudes\DTOs;

class UpdateEstadoSolicitudDTO
{
    public function __construct(
        public readonly string $estado,
        public readonly ?string $motivo_rechazo = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            estado: $data['estado'],
            motivo_rechazo: $data['motivo_rechazo'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = ['estado' => $this->estado];
        
        if ($this->motivo_rechazo) {
            $data['motivo_rechazo'] = $this->motivo_rechazo;
        }
        
        return $data;
    }
}