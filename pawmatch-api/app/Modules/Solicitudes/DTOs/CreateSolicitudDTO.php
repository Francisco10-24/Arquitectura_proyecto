<?php

namespace App\Modules\Solicitudes\DTOs;

class CreateSolicitudDTO
{
    public function __construct(
        public readonly int $user_id,
        public readonly int $mascota_id,
        public readonly ?string $comentarios_adoptante = null,
        public readonly string $estado = 'PENDIENTE'
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            user_id: $userId,
            mascota_id: (int) $data['mascota_id'],
            comentarios_adoptante: $data['comentarios_adoptante'] ?? null,
            estado: 'PENDIENTE'
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'mascota_id' => $this->mascota_id,
            'comentarios_adoptante' => $this->comentarios_adoptante,
            'estado' => $this->estado,
        ];
    }
}