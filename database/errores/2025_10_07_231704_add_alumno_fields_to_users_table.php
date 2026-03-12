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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('curp')->unique()->nullable(); // Identificador institucional
            $table->string('cuip')->nullable();
            $table->string('cup')->nullable();
            $table->string('dependencia')->nullable();
            $table->string('adscripcion')->nullable();
            $table->string('perfil')->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('fotografia')->nullable(); // Ruta del archivo
            $table->string('tipo')->default('alumno'); // Distinción institucional

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
