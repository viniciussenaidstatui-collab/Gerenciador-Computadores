<?php

namespace Tests\Feature;

use App\Models\Computador;
use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComputadorControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('computadores.create'))
            ->assertRedirect(route('login'));
    }

    public function test_create_page_displays_the_authenticated_usuario_data(): void
    {
        $usuario = $this->usuarioAutenticado();

        $this->get(route('computadores.create'))
            ->assertOk()
            ->assertSee([$usuario->nome, $usuario->cpf, $usuario->email]);
    }

    public function test_valid_request_registers_computer_for_authenticated_usuario(): void
    {
        $usuario = $this->usuarioAutenticado();

        $this->post(route('computadores.store'), [
            'numero' => 20,
            'senha_confirmacao' => 'senha-segura',
        ])->assertRedirect(route('computadores.create'))
            ->assertSessionHas('success', 'Computador registrado com sucesso.');

        $this->assertDatabaseHas('computadores', [
            'numero' => 20,
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_duplicate_computer_number_returns_clear_error_and_creates_no_record(): void
    {
        $outroUsuario = $this->criarUsuario('Outro Usuário', 'outro@example.com', '98765432100');
        Computador::query()->create([
            'numero' => 20,
            'usuario_id' => $outroUsuario->id,
        ]);

        $this->usuarioAutenticado();

        $this->from(route('computadores.create'))
            ->post(route('computadores.store'), [
                'numero' => 20,
                'senha_confirmacao' => 'senha-segura',
            ])->assertRedirect(route('computadores.create'))
            ->assertSessionHasErrors([
                'numero' => 'Este número de computador já está registrado.',
            ]);

        $this->assertSame(1, Computador::query()->count());
    }

    public function test_incorrect_password_does_not_register_computer(): void
    {
        $this->usuarioAutenticado();

        $this->from(route('computadores.create'))
            ->post(route('computadores.store'), [
                'numero' => 20,
                'senha_confirmacao' => 'senha-incorreta',
            ])->assertRedirect(route('computadores.create'))
            ->assertSessionHasErrors([
                'senha_confirmacao' => 'A senha informada está incorreta.',
            ]);

        $this->assertDatabaseMissing('computadores', ['numero' => 20]);
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

        $this->withSession([
            'usuario_id' => $usuario->id,
            'token_usuario' => $token,
        ]);

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
