<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tipo_movimiento'); // 'cambio_nivel', 'cambio_adscripcion', 'cambio_plantel'
            
            // Estado Anterior
            $table->string('nivel_anterior')->nullable();
            $table->jsonb('perfil_data_anterior')->nullable();
            $table->foreignId('plantel_id_anterior')->nullable()->constrained('planteles');
            
            // Estado Nuevo
            $table->string('nivel_nuevo')->nullable();
            $table->jsonb('perfil_data_nuevo')->nullable();
            $table->foreignId('plantel_id_nuevo')->nullable()->constrained('planteles');
            
            $table->text('motivo')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_movimientos');
    }
};
