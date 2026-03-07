<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;
use App\Modules\Auth\DTOs\RegisterUserDTO;

class UserRepository
{
    public function create(RegisterUserDTO $dto): User
    {
        return User::create($dto->toArray());
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}