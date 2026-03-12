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
        Schema::create('configuracion_institucional', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_institucion');
            $table->string('siglas')->nullable();
            $table->string('rfc')->nullable();
            $table->string('domicilio_fiscal')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->string('correo_contacto')->nullable();
            $table->string('pagina_web')->nullable();
            $table->string('logo_path')->nullable(); // Ruta del logo institucional
            $table->text('leyenda_documentos')->nullable(); // Para constancias, certificados, etc.
            $table->json('parametros_adicionales')->nullable(); // Para extensibilidad futura
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_institucional');
    }
};
