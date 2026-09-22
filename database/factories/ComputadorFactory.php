<?php

namespace Database\Factories;

use App\Models\Computador;
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
            'numero_patrimonio' => fake()->unique()->bothify('PAT-#####'),
            'status' => 'disponivel',
        ];
    }
}
