<?php

namespace App\Modules\Auth\DTOs;

class RegisterUserDTO
{
    // Constructor con atributos
    public function __construct(
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $telefono = null,
        public readonly ?string $direccion = null,
        public readonly string $rol = 'USUARIO'
    ) {}

    // Método para convertir los datos de la request a un objeto
    public static function fromRequest(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            email: $data['email'],
            password: $data['password'],
            telefono: $data['telefono'] ?? null,
            direccion: $data['direccion'] ?? null,
            rol: $data['rol'] ?? 'USUARIO'
        );
    }

    // Método para convertir el objeto a un array
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'rol' => $this->rol,
        ];
    }
}