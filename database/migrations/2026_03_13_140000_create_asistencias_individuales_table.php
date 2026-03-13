<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_individuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->foreignId('asistencia_id')->nullable()->constrained('asistencias')->onDelete('set null');
            $table->date('fecha');
            $table->string('estatus')->default('falta'); // presente, falta, permiso, retardo
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'grupo_id', 'fecha'], 'unique_asistencia_diaria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_individuales');
    }
};
