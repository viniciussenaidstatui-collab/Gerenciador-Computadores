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
        Schema::create('rfid', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_code')->unique();
            $table->timestamp('retirado')->nullable();
            $table->timestamp('devolvido')->nullable();
            $table->string('usuario_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
