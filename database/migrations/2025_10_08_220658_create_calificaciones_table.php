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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained();
            $table->foreignId('alumno_id')->constrained('users');
            $table->string('unidad'); // Ej. Unidad 1, Unidad 2, Final
            $table->decimal('calificacion', 5, 2); // Ej. 8.5
            $table->text('observaciones')->nullable();

                    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
