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
        Schema::table('grupos', function (Blueprint $table) {
           // Fechas de inicio y término del grupo
            $table->date('fecha_inicio')->nullable()->after('estado');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');

            // Horario del grupo
            $table->time('hora_inicio')->nullable()->after('fecha_fin');
            $table->time('hora_fin')->nullable()->after('hora_inicio');

            // Total de horas del curso
            $table->integer('total_horas')->nullable()->after('hora_fin');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio','fecha_fin','hora_inicio','hora_fin','total_horas']);
        });
    }
};
