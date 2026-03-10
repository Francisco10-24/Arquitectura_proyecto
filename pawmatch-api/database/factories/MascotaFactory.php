<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mascota>
 */
class MascotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'especie' => fake()->randomElement(['PERRO', 'GATO', 'OTRO']),
            'raza' => fake()->word(),
            'edad_aproximada' => fake()->numberBetween(6, 120),
            'sexo' => fake()->randomElement(['MACHO', 'HEMBRA']),
            'descripcion' => fake()->sentence(),
            'foto_url' => fake()->imageUrl(640, 480, 'animals', true),
            'estado' => 'DISPONIBLE',
        ];
    }
}
