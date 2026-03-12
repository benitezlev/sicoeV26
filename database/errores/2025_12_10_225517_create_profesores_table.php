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
        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('externo_id')->nullable()->index(); // ID externo de la API
            $table->string('nombre');
            $table->string('curp')->unique();
            $table->foreignId('plantel_id')->constrained()->onDelete('cascade');
            $table->string('estatus')->default('activo');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesores');
    }
};
