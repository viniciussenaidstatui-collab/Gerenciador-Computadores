<?php

namespace Database\Factories;

use App\Models\Computador;
use App\Models\Emprestimo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Emprestimo>
 */
class EmprestimoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'computador_id' => Computador::factory(),
            'retirado_em' => now()->subHour(),
            'devolvido_em' => null,
        ];
    }
}
