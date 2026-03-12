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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            // Aquí corregimos el nombre de la tabla referenciada
            $table->foreignId('plantel_id')
                ->constrained('planteles')
                ->onDelete('cascade');
            $table->string('archivo')->nullable();
            $table->enum('estado', ['no_subido','pendiente','validado','rechazado','expirado'])->default('no_subido');
            $table->timestamp('fecha_inicio_real');
            $table->timestamp('fecha_validacion_externa')->nullable();
            $table->timestamp('subido_at')->nullable();
            $table->timestamp('validado_at')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
