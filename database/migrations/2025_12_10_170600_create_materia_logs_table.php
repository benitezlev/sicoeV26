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
        Schema::create('materia_logs', function (Blueprint $table) {
             $table->id();

            // Relación con la materia
            $table->foreignId('materia_id')->constrained()->onDelete('cascade');

            // Usuario que realizó la acción
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Tipo de acción: create, update, delete
            $table->string('accion');

            // Datos previos y nuevos (JSON para flexibilidad)
            $table->json('datos_previos')->nullable();
            $table->json('datos_nuevos')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_logs');
    }
};
