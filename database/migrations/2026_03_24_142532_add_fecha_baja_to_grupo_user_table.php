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
        Schema::table('grupo_user', function (Blueprint $table) {
            $table->dateTime('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->foreignId('baja_registrada_por')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupo_user', function (Blueprint $table) {
            $table->dropColumn(['fecha_baja', 'motivo_baja', 'baja_registrada_por']);
        });
    }
};
