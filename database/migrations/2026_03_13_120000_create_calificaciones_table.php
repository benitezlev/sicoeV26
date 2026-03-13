<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Alumno
            $table->foreignId('grupo_id')->constrained()->onDelete('cascade');
            $table->foreignId('materia_id')->constrained()->onDelete('cascade');
            $table->string('unidad'); // 1, 2, 3, Extraordinario, etc.
            $table->decimal('calificacion', 5, 2);
            $table->foreignId('registrado_por')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'grupo_id', 'materia_id', 'unidad'], 'unique_calificacion_unidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
