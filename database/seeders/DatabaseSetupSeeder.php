<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSetupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caches de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Permisos de Operación
        $permisos = [
            'configuarion.total',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.baja',
            'grupos.ver', 'grupos.crear', 'grupos.editar', 'grupos.baja',
            'asistencias.tomar', 'asistencias.ver',
            'calificaciones.asignar', 'calificaciones.ver',
            'reportes.descargar', 'actas.subir_certificadas',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 3. Crear Roles
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $adminTi = Role::firstOrCreate(['name' => 'admin_ti', 'guard_name' => 'web']);
        $controlEscolar = Role::firstOrCreate(['name' => 'control_escolar', 'guard_name' => 'web']);
        $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);

        // 4. Asignar Permisos a Roles
        // SuperAdmin & Control Escolar tienen TODO
        $superAdmin->syncPermissions(Permission::all());
        $adminTi->syncPermissions(Permission::all());
        $controlEscolar->syncPermissions(Permission::all());

        // Operador tiene acceso limitado según lo pedido
        $operador->syncPermissions([
            'grupos.ver',
            'asistencias.tomar',
            'asistencias.ver',
            'calificaciones.asignar',
            'calificaciones.ver',
            'reportes.descargar',
            'actas.subir_certificadas',
        ]);

        // 5. Crear Usuarios del Sistema
        $this->crearUsuario('Super Administrador', 'superadmin@sicoe.mx', 'Admin_2026', 'superadmin');
        $this->crearUsuario('Control Escolar', 'control_escolar@sicoe.mx', 'Control_2026', 'control_escolar');
        $this->crearUsuario('Operador SICOE', 'operador@sicoe.mx', 'Operador_2026', 'operador');

        $this->command->info('Base de Datos configurada con éxito.');
    }

    private function crearUsuario($nombre, $email, $password, $role)
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nombre' => $nombre,
                'paterno' => 'Sistema',
                'materno' => 'SICOE',
                'username' => explode('@', $email)[0],
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'tipo' => 'empleado',
                'nivel' => 'estatal',
            ]
        );
        $user->assignRole($role);
    }
}
