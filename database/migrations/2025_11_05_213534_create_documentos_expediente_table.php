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
        Schema::create('documentos_expediente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('expediente_id')->constrained()->onDelete('cascade');
            $table->string('tipo'); // Ej: ACTA, CONSTANCIA, OFICIO
            $table->string('archivo'); // Ruta del archivo en storage
            $table->timestamp('fecha_carga')->nullable();
            $table->foreignId('cargado_por')->nullable()->constrained('users');
            $table->string('estatus')->default('pendiente'); // pendiente, validado, observado
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_expediente');
    }
};
