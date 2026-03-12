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
        Schema::create('curso_materia', function (Blueprint $table) {
           $table->id();

            // Relaciones
            $table->foreignId('curso_id')->constrained()->onDelete('cascade');
            $table->foreignId('materia_id')->constrained()->onDelete('cascade');

            // Campos adicionales para la tira académica
            $table->integer('orden')->nullable();       // posición dentro de la secuencia
            $table->integer('semestre')->nullable();    // semestre o ciclo académico
            $table->integer('creditos')->nullable();    // carga académica en créditos
            $table->boolean('obligatoria')->default(true); // si es materia obligatoria u optativa
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_materia');
    }
};
