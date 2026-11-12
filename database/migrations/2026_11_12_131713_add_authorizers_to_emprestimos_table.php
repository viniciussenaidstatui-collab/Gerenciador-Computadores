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
        Schema::table('emprestimos', function (Blueprint $table): void {
            $table->foreignId('retirada_autorizada_por')->nullable()->constrained('responsaveis')->nullOnDelete();
            $table->foreignId('devolucao_autorizada_por')->nullable()->constrained('responsaveis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emprestimos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('devolucao_autorizada_por');
            $table->dropConstrainedForeignId('retirada_autorizada_por');
        });
    }
};
