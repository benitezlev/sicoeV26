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
        Schema::create('metas_capacitacion', function (Blueprint $table) {
            $table->id();
            $table->integer('anio')->unique(); // Ciclo Fiscal (ej: 2024, 2025, 2026)
            $table->integer('meta'); // Meta de capacitados (ej: 3000)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas_capacitacion');
    }
};
