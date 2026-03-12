<?php

namespace App\Modules\Auth\DTOs;

class LoginUserDTO
{
    // Constructor con atributos
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    // Método para convertir los datos de la request a un objeto
    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password']
        );
    }
}