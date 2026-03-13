<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('municipio_id')->nullable()->constrained()->onDelete('set null');
        });

        // Migrar datos existentes de perfil_data a la columna real
        $users = User::all();
        foreach ($users as $user) {
            if (isset($user->perfil_data['municipio_id'])) {
                $user->update(['municipio_id' => $user->perfil_data['municipio_id']]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipio_id');
        });
    }
};
