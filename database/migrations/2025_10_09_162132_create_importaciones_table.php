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
        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->string('modulo'); // Ej. 'cursos'
            $table->string('archivo'); // Nombre del archivo
            $table->foreignId('user_id')->constrained(); // Usuario que importó
            $table->integer('registros')->default(0);
            $table->integer('duplicados')->default(0);
            $table->integer('errores')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importaciones');
    }
};
