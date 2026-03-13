<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_requeridos', function (Blueprint $table) {
            $table->string('nivel')->nullable()->after('perfil'); // estatal, municipal, fiscalia, administrativo
            $table->string('descripcion')->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_requeridos', function (Blueprint $table) {
            $table->dropColumn(['nivel', 'descripcion']);
        });
    }
};
