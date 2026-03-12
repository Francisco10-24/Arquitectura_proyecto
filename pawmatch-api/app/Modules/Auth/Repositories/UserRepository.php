<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;
use App\Modules\Auth\DTOs\RegisterUserDTO;

class UserRepository
{
    // Método para crear un usuario
    public function create(RegisterUserDTO $dto): User
    {
        return User::create($dto->toArray());
    }

    // Método para buscar un usuario por email
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    // Método para buscar un usuario por id
    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}