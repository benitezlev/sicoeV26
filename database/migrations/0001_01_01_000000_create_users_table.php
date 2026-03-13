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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('paterno');
            $table->string('materno')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            
            // Identidad y Seguridad
            $table->string('username')->unique();
            $table->string('curp')->unique()->nullable();
            $table->string('cuip')->unique()->nullable();
            $table->string('cup')->unique()->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            // Estructura Multi-Nivel
            $table->enum('nivel', ['estatal', 'municipal', 'fiscalia', 'administrativo'])->default('estatal');
            $table->jsonb('perfil_data')->nullable(); // Para campos específicos de cada nivel
            
            // Datos institucionales comunes
            $table->string('tipo')->default('alumno'); // docente, alumno, admin
            $table->enum('sexo', ['H', 'M'])->nullable();
            $table->foreignId('plantel_id')->nullable()->constrained('planteles')->nullOnDelete();
            
            $table->text('firma_digital')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
