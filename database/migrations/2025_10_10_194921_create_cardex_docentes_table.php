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
        Schema::create('cardex_docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('grado_academico')->nullable();
            $table->string('especialidad')->nullable();
            $table->text('experiencia_docente')->nullable();
            $table->text('certificaciones')->nullable();
            $table->text('formacion_complementaria')->nullable();
            $table->text('observaciones')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardex_docentes');
    }
};
