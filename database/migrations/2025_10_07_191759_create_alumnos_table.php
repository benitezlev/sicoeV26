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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('cuip')->nullable();
            $table->string('curp')->unique(); // Validación de unicidad
            $table->string('cup')->nullable();
            $table->string('nombre');
            $table->string('dependencia')->nullable();
            $table->string('adscripcion')->nullable();
            $table->string('perfil')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('fotografia')->nullable(); // Ruta del archivo

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
