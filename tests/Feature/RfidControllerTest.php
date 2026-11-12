<?php

namespace Tests\Feature;

use App\Models\Computador;
use App\Models\Emprestimo;
use App\Models\Responsavel;
use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RfidControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.esp32.device_token' => 'token-de-teste']);
        Cache::flush();
    }

    public function test_reader_returns_401_json_for_a_missing_or_invalid_device_token(): void
    {
        $this->postJson('/api/rfid/ler', ['UID' => 'A1B2C3D4'])
            ->assertUnauthorized()
            ->assertJsonPath('sucesso', false);

        $this->withHeader('X-DEVICE-TOKEN', 'token-errado')
            ->postJson('/api/rfid/ler', ['UID' => 'A1B2C3D4'])
            ->assertUnauthorized()
            ->assertJsonPath('sucesso', false);
    }

    public function test_reader_returns_422_json_when_uid_is_missing_even_without_accept_header(): void
    {
        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->post('/api/rfid/ler')
            ->assertUnprocessable()
            ->assertJsonPath('sucesso', false)
            ->assertJsonPath('mensagem', 'UID ausente ou inválido.');
    }

    public function test_reader_returns_404_for_an_unknown_or_inactive_responsible_card(): void
    {
        $inactiveResponsavel = Responsavel::factory()->create(['ativo' => false]);

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => '11223344'])
            ->assertNotFound()
            ->assertJsonPath('sucesso', false);

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $inactiveResponsavel->rfid_uid])
            ->assertNotFound()
            ->assertJsonPath('mensagem', 'Cartão não cadastrado ou responsável inativo.');
    }

    public function test_reader_returns_409_when_no_computer_selection_is_pending(): void
    {
        $responsavel = Responsavel::factory()->create();

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $responsavel->rfid_uid])
            ->assertConflict()
            ->assertJsonPath('sucesso', false);
    }

    public function test_reader_returns_409_when_computer_selection_has_expired(): void
    {
        $this->autenticarUsuario();
        $responsavel = Responsavel::factory()->create();
        $computador = Computador::factory()->create();

        $this->post(route('computadores.selecionar', $computador))->assertOk();
        $this->travel(61)->seconds();

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $responsavel->rfid_uid])
            ->assertConflict()
            ->assertJsonPath('sucesso', false);
    }

    public function test_reader_withdraws_then_returns_the_selected_computer_and_records_authorizers(): void
    {
        $usuario = $this->autenticarUsuario();
        $responsavel = Responsavel::factory()->create(['nome' => 'Professor 1']);
        $computador = Computador::factory()->create(['numero_patrimonio' => 'PC-05']);

        $this->get(route('emprestimos.create'))
            ->assertOk()
            ->assertSee('Computadores')
            ->assertSee('PC-05');

        $this->post(route('computadores.selecionar', $computador))
            ->assertOk()
            ->assertJsonPath('sucesso', true);

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => strtolower($responsavel->rfid_uid)])
            ->assertOk()
            ->assertExactJson([
                'sucesso' => true,
                'acao' => 'retirada',
                'mensagem' => 'Retirada do PC-05 autorizada por Professor 1.',
            ]);

        $emprestimo = Emprestimo::query()->where('computador_id', $computador->id)->firstOrFail();
        $this->assertSame($usuario->id, $emprestimo->usuario_id);
        $this->assertSame($responsavel->id, $emprestimo->retirada_autorizada_por);
        $this->assertNull($emprestimo->devolvido_em);
        $this->assertDatabaseHas('computadores', ['id' => $computador->id, 'status' => 'em_uso']);

        $this->post(route('computadores.selecionar', $computador))->assertOk();

        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $responsavel->rfid_uid])
            ->assertOk()
            ->assertExactJson([
                'sucesso' => true,
                'acao' => 'devolucao',
                'mensagem' => 'Devolução do PC-05 autorizada por Professor 1.',
            ]);

        $emprestimo->refresh();
        $this->assertNotNull($emprestimo->devolvido_em);
        $this->assertSame($responsavel->id, $emprestimo->devolucao_autorizada_por);
        $this->assertDatabaseHas('computadores', ['id' => $computador->id, 'status' => 'disponivel']);

        $this->withCredentials()->getJson(route('rfid.status'))
            ->assertOk()
            ->assertJsonPath('pendente', false)
            ->assertJsonPath('resultado.acao', 'devolucao');

        $this->get(route('emprestimos.historico'))
            ->assertOk()
            ->assertSee('Professor 1')
            ->assertSee('PC-05');
    }

    public function test_another_user_cannot_replace_a_pending_computer_selection(): void
    {
        $computador = Computador::factory()->create();
        $this->autenticarUsuario('aluno1@example.com', '11111111111');

        $this->post(route('computadores.selecionar', $computador))->assertOk();

        $this->autenticarUsuario('aluno2@example.com', '22222222222');

        $this->post(route('computadores.selecionar', $computador))
            ->assertConflict()
            ->assertJsonPath('mensagem', 'Aguarde, outra seleção em andamento.');
    }

    public function test_only_an_admin_can_manage_responsible_cards(): void
    {
        $this->autenticarUsuario();

        $this->get(route('admin.responsaveis.index'))->assertForbidden();

        $this->autenticarUsuario('admin@example.com', '33333333333', true);

        $this->get(route('admin.responsaveis.index'))->assertOk();

        $this->post(route('admin.responsaveis.store'), [
            'nome' => 'Professor 2',
            'rfid_uid' => 'a1b2c3d4',
            'acao_permitida' => 'ambas',
        ])->assertRedirect(route('admin.responsaveis.index'));

        $responsavel = Responsavel::query()->where('rfid_uid', 'A1B2C3D4')->firstOrFail();

        $this->get(route('admin.responsaveis.edit', $responsavel))->assertOk();
        $this->put(route('admin.responsaveis.update', $responsavel), [
            'nome' => 'Professor Atualizado',
            'rfid_uid' => $responsavel->rfid_uid,
            'acao_permitida' => 'ambas',
        ])->assertRedirect(route('admin.responsaveis.index'));

        $this->patch(route('admin.responsaveis.ativo', $responsavel))
            ->assertRedirect(route('admin.responsaveis.index'));

        $this->assertDatabaseHas('responsaveis', [
            'id' => $responsavel->id,
            'nome' => 'Professor Atualizado',
            'ativo' => false,
        ]);
    }

    public function test_withdrawal_and_return_cards_are_restricted_to_their_configured_action(): void
    {
        $this->autenticarUsuario();
        $cartaoRetirada = Responsavel::factory()->create([
            'nome' => 'Cartao de emprestimo',
            'rfid_uid' => '43E67D14',
            'acao_permitida' => 'retirada',
        ]);
        $cartaoDevolucao = Responsavel::factory()->create([
            'nome' => 'Cartao de devolucao',
            'rfid_uid' => '935BE52C',
            'acao_permitida' => 'devolucao',
        ]);
        $computador = Computador::factory()->create();

        $this->post(route('computadores.selecionar', $computador))->assertOk();
        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $cartaoDevolucao->rfid_uid])
            ->assertConflict()
            ->assertJsonPath('mensagem', 'Este cartao autoriza somente devolucao.');
        $this->assertDatabaseMissing('emprestimos', ['computador_id' => $computador->id]);

        $this->post(route('computadores.selecionar', $computador))->assertOk();
        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $cartaoRetirada->rfid_uid])
            ->assertOk()
            ->assertJsonPath('acao', 'retirada');

        $this->post(route('computadores.selecionar', $computador))->assertOk();
        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $cartaoRetirada->rfid_uid])
            ->assertConflict()
            ->assertJsonPath('mensagem', 'Este cartao autoriza somente retirada.');
        $this->assertDatabaseHas('emprestimos', [
            'computador_id' => $computador->id,
            'devolvido_em' => null,
        ]);

        $this->post(route('computadores.selecionar', $computador))->assertOk();
        $this->withHeader('X-DEVICE-TOKEN', 'token-de-teste')
            ->postJson('/api/rfid/ler', ['UID' => $cartaoDevolucao->rfid_uid])
            ->assertOk()
            ->assertJsonPath('acao', 'devolucao');
    }

    private function autenticarUsuario(
        string $email = 'aluno@example.com',
        string $cpf = '12345678901',
        bool $isAdmin = false,
    ): Usuario {
        $usuario = Usuario::query()->create([
            'nome' => 'Aluno Teste',
            'email' => $email,
            'senha' => Hash::make('senha-segura'),
            'cpf' => $cpf,
            'data_nascimento' => '2000-01-01',
        ]);
        $usuario->is_admin = $isAdmin;
        $usuario->save();
        $token = 'token-de-teste-'.$usuario->id;

        TokenUsuario::query()->create([
            'usuario_id' => $usuario->id,
            'token' => $token,
            'valido_ate' => now()->addDay(),
        ]);

        $this->withUnencryptedCookie('token_usuario', $token);

        return $usuario;
    }
}
