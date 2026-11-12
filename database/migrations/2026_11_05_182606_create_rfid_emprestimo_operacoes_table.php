<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_emprestimo_operacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('computador_id')->constrained('computadores')->cascadeOnDelete();
            $table->foreignId('solicitado_por_usuario_id')->constrained('usuario')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->string('status', 32)->default('aguardando');
            $table->string('mensagem')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_emprestimo_operacoes');
    }
};
