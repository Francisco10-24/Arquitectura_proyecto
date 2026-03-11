<?php

namespace Database\Factories;

use App\Models\Mascota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolicitudAdopcionFactory extends Factory
{
    protected $model = \App\Models\SolicitudAdopcion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mascota_id' => Mascota::factory(),
            'estado' => 'PENDIENTE',
            'comentarios_adoptante' => fake()->sentence(),
        ];
    }
}
