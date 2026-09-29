<?php

namespace Tests\Feature;

use App\Models\Computador;
use App\Models\Emprestimo;
use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmprestimoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_usuario_can_withdraw_available_computer(): void
    {
        $usuario = $this->usuarioAutenticado();
        $computador = Computador::factory()->create();

        $this->post(route('emprestimos.store'), [
            'computador_id' => $computador->id,
        ])->assertRedirect(route('emprestimos.create'))
            ->assertSessionHas('success', 'Computador retirado com sucesso.');

        $this->assertDatabaseHas('emprestimos', [
            'computador_id' => $computador->id,
            'usuario_id' => $usuario->id,
            'devolvido_em' => null,
        ]);
        $this->assertDatabaseHas('computadores', [
            'id' => $computador->id,
            'status' => 'em_uso',
        ]);
    }

    public function test_unavailable_computer_cannot_be_withdrawn(): void
    {
        $this->usuarioAutenticado();
        $computador = Computador::factory()->create(['status' => 'em_uso']);

        $this->from(route('emprestimos.create'))
            ->post(route('emprestimos.store'), [
                'computador_id' => $computador->id,
            ])->assertRedirect(route('emprestimos.create'))
            ->assertSessionHasErrors([
                'computador_id' => 'Este computador não está disponível para retirada.',
            ]);

        $this->assertDatabaseMissing('emprestimos', ['computador_id' => $computador->id]);
    }

    public function test_nonexistent_computer_cannot_be_withdrawn(): void
    {
        $this->usuarioAutenticado();

        $this->from(route('emprestimos.create'))
            ->post(route('emprestimos.store'), [
                'computador_id' => 999999,
            ])->assertRedirect(route('emprestimos.create'))
            ->assertSessionHasErrors('computador_id');
    }

    public function test_authenticated_usuario_can_return_own_active_loan(): void
    {
        $usuario = $this->usuarioAutenticado();
        $computador = Computador::factory()->create(['status' => 'em_uso']);
        $emprestimo = Emprestimo::factory()->create([
            'computador_id' => $computador->id,
            'usuario_id' => $usuario->id,
            'retirado_em' => now()->subHour(),
        ]);

        $this->patch(route('emprestimos.devolver', $emprestimo))
            ->assertRedirect(route('emprestimos.create'))
            ->assertSessionHas('success', 'Computador devolvido com sucesso.');

        $this->assertNotNull($emprestimo->fresh()->devolvido_em);
        $this->assertDatabaseHas('computadores', [
            'id' => $computador->id,
            'status' => 'disponivel',
        ]);
    }

    public function test_usuario_cannot_return_another_usuario_loan(): void
    {
        $this->usuarioAutenticado();
        $outroUsuario = $this->criarUsuario('Outro Usuário', 'outro@example.com', '98765432100');
        $computador = Computador::factory()->create(['status' => 'em_uso']);
        $emprestimo = Emprestimo::factory()->create([
            'computador_id' => $computador->id,
            'usuario_id' => $outroUsuario->id,
        ]);

        $this->patch(route('emprestimos.devolver', $emprestimo))
            ->assertNotFound();

        $this->assertDatabaseHas('emprestimos', [
            'id' => $emprestimo->id,
            'devolvido_em' => null,
        ]);
    }

    private function usuarioAutenticado(): Usuario
    {
        $usuario = $this->criarUsuario('Carlos', 'carlos@example.com', '12345678900');
        $token = 'token-de-teste-'.$usuario->id;

        TokenUsuario::query()->create([
            'usuario_id' => $usuario->id,
            'token' => $token,
            'valido_ate' => now()->addDay(),
        ]);

        $this->withUnencryptedCookie('token_usuario', $token);

        return $usuario;
    }

    private function criarUsuario(string $nome, string $email, string $cpf): Usuario
    {
        return Usuario::query()->create([
            'nome' => $nome,
            'email' => $email,
            'senha' => Hash::make('senha-segura'),
            'cpf' => $cpf,
            'data_nascimento' => '1990-01-01',
        ]);
    }
}
