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

    public function test_web_withdrawal_registers_the_selected_computer_for_the_scanned_user(): void
    {
        $this->usuarioAutenticado();
        $usuario = $this->criarUsuario('Rafael', 'rafael@example.com', '12345678901', 'A1B2C3D4');
        $computador = Computador::factory()->create();

        $response = $this->withCredentials()->postJson(route('emprestimos.store'), [
            'computador_id' => $computador->id,
            'usuario_rfid_uid' => 'a1:b2:c3:d4',
        ]);
        $response->assertCreated()
            ->assertJsonPath('usuario.nome', 'Rafael')
            ->assertJsonPath('computador.id', $computador->id);

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

    public function test_rfid_loan_authorizes_registered_user_and_available_computer(): void
    {
        config(['services.esp32.device_token' => 'token-esp32']);
        $usuario = $this->criarUsuario('Carlos', 'carlos@example.com', '12345678900', 'A1B2C3D4');
        $computador = Computador::factory()->create(['rfid_uid' => 'AABBCCDD']);

        $this->withHeader('X-DEVICE-TOKEN', 'token-esp32')
            ->postJson('/api/rfid/emprestimo', [
                'usuario_rfid_uid' => 'a1:b2:c3:d4',
                'computador_rfid_uid' => 'aa-bb-cc-dd',
            ])->assertCreated()
            ->assertJsonPath('status', 'sucesso');

        $this->assertDatabaseHas('emprestimos', [
            'computador_id' => $computador->id,
            'usuario_id' => $usuario->id,
            'devolvido_em' => null,
        ]);
        $this->assertDatabaseHas('computadores', ['id' => $computador->id, 'status' => 'em_uso']);
    }

    public function test_rfid_loan_rejects_unregistered_user(): void
    {
        config(['services.esp32.device_token' => 'token-esp32']);

        $this->withHeader('X-DEVICE-TOKEN', 'token-esp32')
            ->postJson('/api/rfid/emprestimo', [
                'usuario_rfid_uid' => '00112233',
                'computador_rfid_uid' => 'AABBCCDD',
            ])->assertForbidden()
            ->assertJsonPath('status', 'usuario_nao_autorizado');
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

    private function criarUsuario(string $nome, string $email, string $cpf, ?string $rfidUid = null): Usuario
    {
        return Usuario::query()->create([
            'nome' => $nome,
            'email' => $email,
            'senha' => Hash::make('senha-segura'),
            'cpf' => $cpf,
            'data_nascimento' => '1990-01-01',
            'rfid_uid' => $rfidUid,
        ]);
    }
}
