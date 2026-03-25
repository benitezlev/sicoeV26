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
        Schema::table('configuracion_institucional', function (Blueprint $table) {
            $table->string('pleca_recurso_1')->nullable();
            $table->string('pleca_recurso_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_institucional', function (Blueprint $table) {
            $table->dropColumn(['pleca_recurso_1', 'pleca_recurso_2']);
        });
    }
};
