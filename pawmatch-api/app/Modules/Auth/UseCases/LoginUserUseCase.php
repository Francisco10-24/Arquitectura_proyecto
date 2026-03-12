<?php

namespace App\Modules\Auth\UseCases;

use App\Modules\Auth\DTOs\LoginUserDTO;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserUseCase
{
    // Instancia de la clase del repositorio
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function execute(LoginUserDTO $dto): array
    {
        // Buscar usuario
        $user = $this->userRepository->findByEmail($dto->email);

        // Validar
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.']
            ]);
        }

        // Revocar tokens anteriores
        $user->tokens()->delete();

        // Generar nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Devolver datos del usuario y el token
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
