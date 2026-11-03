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
            $table->string('rfid_uid', 20)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            $table->dropUnique(['rfid_uid']);
            $table->dropColumn('rfid_uid');
        });
    }
};
