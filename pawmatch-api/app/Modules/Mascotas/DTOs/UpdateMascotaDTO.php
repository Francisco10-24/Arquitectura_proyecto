<?php

namespace App\Modules\Mascotas\DTOs;

class UpdateMascotaDTO
{
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?string $especie = null,
        public readonly ?string $raza = null,
        public readonly ?int $edad_aproximada = null,
        public readonly ?string $sexo = null,
        public readonly ?string $descripcion = null,
        public readonly ?string $foto_url = null,
        public readonly ?string $estado = null
    ) {}

    // Form request a partir de los datos del DTO

    public static function fromRequest(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            especie: $data['especie'] ?? null,
            raza: $data['raza'] ?? null,
            edad_aproximada: isset($data['edad_aproximada']) ? (int) $data['edad_aproximada'] : null,
            sexo: $data['sexo'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            foto_url: $data['foto_url'] ?? null,
            estado: $data['estado'] ?? null
        );
    }

    // Array a partir de los datos del DTO

    public function toArray(): array
    {
        return array_filter([
            'nombre' => $this->nombre,
            'especie' => $this->especie,
            'raza' => $this->raza,
            'edad_aproximada' => $this->edad_aproximada,
            'sexo' => $this->sexo,
            'descripcion' => $this->descripcion,
            'foto_url' => $this->foto_url,
            'estado' => $this->estado,
        ], fn($value) => $value !== null);
    }
}