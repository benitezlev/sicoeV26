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
        Schema::create('capacitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cursos_id')->constrained(); // Curso base
            $table->foreignId('planteles_id')->constrained(); // Sede
            $table->foreignId('docentes_id')->constrained('users'); // Instructor
            $table->string('grupo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('codigo_validacion')->nullable();
            $table->string('oficio_validacion')->nullable();
            $table->boolean('subcontratado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacitaciones');
    }
};
