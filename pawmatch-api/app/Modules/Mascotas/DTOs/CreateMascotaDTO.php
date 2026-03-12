<?php

namespace App\Modules\Mascotas\DTOs;

class CreateMascotaDTO
{
    // Constructor con los datos requeridos para crear una mascota
    public function __construct(
        public readonly string $nombre,
        public readonly string $especie,
        public readonly ?string $raza = null,
        public readonly ?int $edad_aproximada = null,
        public readonly ?string $sexo = null,
        public readonly ?string $descripcion = null,
        public readonly ?string $foto_url = null,
        public readonly string $estado = 'DISPONIBLE'
    ) {}

    // Crear un form request a partir de los datos del DTO
    public static function fromRequest(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            especie: $data['especie'],
            raza: $data['raza'] ?? null,
            edad_aproximada: isset($data['edad_aproximada']) ? (int) $data['edad_aproximada'] : null,
            sexo: $data['sexo'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            foto_url: $data['foto_url'] ?? null,  
            estado: $data['estado'] ?? 'DISPONIBLE'
        );
    }
    // Crear un array a partir de los datos del DTO
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'especie' => $this->especie,
            'raza' => $this->raza,
            'edad_aproximada' => $this->edad_aproximada,
            'sexo' => $this->sexo,
            'descripcion' => $this->descripcion,
            'foto_url' => $this->foto_url,
            'estado' => $this->estado,
        ];
    }
}
