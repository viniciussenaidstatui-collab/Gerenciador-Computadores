<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_generates_an_md5_token_without_storing_it_in_the_session(): void
    {
        $usuario = Usuario::query()->create([
            'nome' => 'Carlos',
            'email' => 'carlos@example.com',
            'senha' => Hash::make('senha-segura'),
            'cpf' => '12345678900',
            'data_nascimento' => '1990-01-01',
        ]);

        $response = $this->postJson('/login', [
            'email' => $usuario->email,
            'senha' => 'senha-segura',
        ])->assertOk()
            ->assertJsonPath('erro', 'n')
            ->assertSessionMissing('token_usuario');

        $token = $response->json('token');

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $token);
        $this->assertDatabaseHas('token_usuario', [
            'usuario_id' => $usuario->id,
            'token' => $token,
        ]);
    }
}
