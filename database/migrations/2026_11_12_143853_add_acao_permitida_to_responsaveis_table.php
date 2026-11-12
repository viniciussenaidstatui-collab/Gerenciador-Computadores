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
        Schema::table('responsaveis', function (Blueprint $table): void {
            $table->string('acao_permitida', 12)->default('ambas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responsaveis', function (Blueprint $table): void {
            $table->dropColumn('acao_permitida');
        });
    }
};
