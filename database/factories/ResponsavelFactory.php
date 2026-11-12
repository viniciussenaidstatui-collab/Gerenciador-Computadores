<?php

namespace Database\Factories;

use App\Models\Responsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Responsavel>
 */
class ResponsavelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'rfid_uid' => fake()->unique()->numerify('########'),
            'ativo' => true,
            'acao_permitida' => 'ambas',
            'acao_permitida' => 'ambas',
        ];
    }
}
