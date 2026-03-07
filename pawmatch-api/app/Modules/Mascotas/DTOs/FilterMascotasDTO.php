<?php

namespace App\Modules\Mascotas\DTOs;

class FilterMascotasDTO
{
    public function __construct(
        public readonly ?string $especie = null,
        public readonly ?string $estado = null,
        public readonly ?string $sexo = null,
        public readonly ?string $search = null,
        public readonly int $per_page = 15
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            especie: $data['especie'] ?? null,
            estado: $data['estado'] ?? null,
            sexo: $data['sexo'] ?? null,
            search: $data['search'] ?? null,
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 15
        );
    }
}