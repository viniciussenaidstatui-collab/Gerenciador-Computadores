<?php

namespace Database\Factories;

use App\Models\Computador;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Computador>
 */
class ComputadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => fake()->unique()->numberBetween(1, 999999),
        ];
    }

    public function paraUsuario(Usuario $usuario): static
    {
        return $this->state(fn (): array => [
            'usuario_id' => $usuario->id,
        ]);
    }
}
