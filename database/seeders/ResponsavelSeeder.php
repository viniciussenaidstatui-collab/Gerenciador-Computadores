<?php

namespace Database\Seeders;

use App\Models\Responsavel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class ResponsavelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            '935BE52C' => ['Cartao de devolucao', 'devolucao'],
            '43E67D14' => ['Cartao de emprestimo', 'retirada'],
        ] as $uid => [$nomeCartao, $acaoPermitida]) {
            $responsavel = Responsavel::query()->firstOrNew(['rfid_uid' => $uid]);
            $responsavel->nome ??= $nomeCartao;
            $responsavel->ativo = true;
            $responsavel->acao_permitida = $acaoPermitida;
            $responsavel->save();
        }

        $nome = trim((string) Config::get('services.initial_responsavel.nome'));
        $uid = strtoupper(trim((string) Config::get('services.initial_responsavel.uid')));

        if ($nome === '' || $uid === '') {
            return;
        }

        Responsavel::query()->updateOrCreate(
            ['rfid_uid' => $uid],
            ['nome' => $nome, 'ativo' => true],
        );
    }
}
