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
        Schema::create('documentos_requeridos', function (Blueprint $table) {
            $table->id();
            $table->string('perfil'); // Ej: POLICIA, ADMINISTRATIVO
            $table->string('tipo');   // Ej: ACTA, CONSTANCIA

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_requeridos');
    }
};
