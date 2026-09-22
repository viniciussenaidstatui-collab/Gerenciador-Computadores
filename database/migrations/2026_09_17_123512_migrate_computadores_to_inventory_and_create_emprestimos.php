<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropUnique(['numero']);
            $table->dropColumn('usuario_id');
            $table->renameColumn('numero', 'numero_patrimonio');
        });

        Schema::table('computadores', function (Blueprint $table) {
            $table->string('numero_patrimonio')->change();
            $table->unique('numero_patrimonio');
            $table->string('status', 20)->default('disponivel')->index();
        });

        Schema::create('emprestimos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computador_id')->constrained('computadores')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->timestamp('retirado_em');
            $table->timestamp('devolvido_em')->nullable();
            $table->timestamps();

            $table->index(['computador_id', 'devolvido_em']);
            $table->index(['usuario_id', 'devolvido_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException(
            'Esta migration descarta os vínculos anteriores entre computadores e usuários e não pode ser revertida sem perda de dados.'
        );
    }
};
