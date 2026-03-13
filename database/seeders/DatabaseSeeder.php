<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Desactivar restricciones temporalmente para seeding masivo
        DB::statement("SET session_replication_role = 'replica'");

        $admin = User::create([
            'id' => 1, // Forzar ID 1 para seeders
            'nombre' => 'Administrador',
            'paterno' => 'SICOE',
            'materno' => 'Sistema',
            'username' => 'admin',
            'email' => 'admin@sicoe.gob.mx',
            'password' => bcrypt('L3vid#2026$'),
            'nivel' => 'administrativo',
            'tipo' => 'admin',
        ]);

        $this->call([
            MunicipioSeeder::class,
            PlantelSeeder::class,
            RolesAndPermissionsSeeder::class,
            ConfiguracionInstitucionalSeeder::class,
        ]);

        $admin->assignRole('admin_ti');

        DB::statement("SET session_replication_role = 'origin'");

        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */
    }

}
