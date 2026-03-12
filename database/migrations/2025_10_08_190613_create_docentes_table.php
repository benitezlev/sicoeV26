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
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cuid');
            $table->string('sexo');
            $table->string('curp');
            $table->string('cuip');
            $table->string('tel');
            $table->string('email');
            $table->string('cve_servidor');
            $table->string('adscrip');
            $table->string('plantel');
            $table->string('cargo');
            $table->string('puesto');
            $table->date('ingreso');
            $table->string('grado_estudio');
            $table->string('acredita');
            $table->string('cedula');
            $table->string('campo_estudio');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
