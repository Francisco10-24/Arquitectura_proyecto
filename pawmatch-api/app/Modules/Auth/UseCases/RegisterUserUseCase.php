<?php

namespace App\Modules\Auth\UseCases;

use App\Modules\Auth\DTOs\RegisterUserDTO;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class RegisterUserUseCase
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function execute(RegisterUserDTO $dto): array
    {
        if ($this->userRepository->findByEmail($dto->email)) {
            throw ValidationException::withMessages([
                'email' => ['El correo electrónico ya está registrado.']
            ]);
        }

        // Crear usuario
        $user = $this->userRepository->create($dto);

        // Generar token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->rol,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }
}