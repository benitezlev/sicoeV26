<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Roles
        $adminTI = Role::create(['name' => 'admin_ti']);
        $controlEscolar = Role::create(['name' => 'control_escolar']);
        $docente = Role::create(['name' => 'docente']);
        $alumno = Role::create(['name' => 'alumno']);

        // Permisos
        Permission::create(['name' => 'ver configuracion']);
        Permission::create(['name' => 'editar configuracion']);
        Permission::create(['name' => 'gestionar alumnos']);
        Permission::create(['name' => 'gestionar docentes']);
        Permission::create(['name' => 'gestionar asignaturas']);
        Permission::create(['name' => 'gestionar calificaciones']);
        Permission::create(['name' => 'ver perfil']);
        Permission::create(['name' => 'ver calificaciones']);
        Permission::create(['name' => 'descargar documentos']);



        // Asignar permisos
        $adminTI->givePermissionTo([
            'ver configuracion',
            'editar configuracion',
            'gestionar alumnos',
            'gestionar docentes',
            'gestionar asignaturas',
            'gestionar calificaciones',
        ]);

        $controlEscolar->givePermissionTo([
            'gestionar alumnos',
            'gestionar docentes',
            'gestionar asignaturas',
            'gestionar calificaciones',
        ]);

        $docente->givePermissionTo([
            'gestionar calificaciones',
        ]);

        $alumno->givePermissionTo([
            'ver perfil',
            'ver calificaciones',
            'descargar documentos',
        ]);
        

    }
}
