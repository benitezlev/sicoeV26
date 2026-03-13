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
        Schema::table('configuracion_institucional', function (Blueprint $table) {
            if (!Schema::hasColumn('configuracion_institucional', 'titular_ums')) {
                $table->string('titular_ums')->nullable();
                $table->string('puesto_titular')->nullable();
                $table->string('siglas_departamento')->nullable();
                $table->text('objetivo_institucional')->nullable();
                $table->string('aviso_privacidad_url')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_institucional', function (Blueprint $table) {
            $table->dropColumn([
                'titular_ums',
                'puesto_titular',
                'siglas_departamento',
                'objetivo_institucional',
                'aviso_privacidad_url'
            ]);
        });
    }
};
