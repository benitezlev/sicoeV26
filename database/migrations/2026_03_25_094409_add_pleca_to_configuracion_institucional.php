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
            if (!Schema::hasColumn('configuracion_institucional', 'pleca_path')) {
                $table->string('pleca_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_institucional', function (Blueprint $table) {
            $table->dropColumn('pleca_path');
        });
    }
};
