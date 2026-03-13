<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateDataToPostgres extends Command
{
    protected $signature = 'db:migrate-to-pg';
    protected $description = 'Migra datos de MySQL a PostgreSQL preservando IDs';

    public function handle()
    {
        try {
        $tables = [
            'configuracion_institucional',
            'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
            'planteles', 'users', 'alumnos', 'docentes', 'certificaciones', 'capacitaciones',
            'expedientes', 'materias', 'grupos', 'profesores', 'profesor_logs', 'materia_logs',
            'curso_materia', 'cursos', 'grupo_user', 'grupo_alumno', 'grupo_expediente', 'grupo_logs',
            'documentos', 'documentos_expediente', 'documentos_requeridos', 'asistencias', 'calificaciones',
            'importaciones'
        ];

        $mysql = DB::connection('mysql');
        $pgsql = DB::connection('pgsql');
        
        // Desactivar restricciones temporalmente
        $pgsql->statement("SET session_replication_role = 'replica'");

        foreach ($tables as $table) {
            $this->info("Migrando tabla: {$table}");
            
            if (!Schema::connection('pgsql')->hasTable($table)) {
                $this->error("Tabla {$table} no existe en Postgres. Saltando...");
                continue;
            }

            $data = $mysql->table($table)->get();
            
            if ($data->isEmpty()) {
                $this->warn("Tabla {$table} está vacía.");
                continue;
            }

            $pgsql->table($table)->delete();
            try {
                foreach ($data->chunk(100) as $chunk) {
                    $preparedChunk = array_map(function($item) {
                        return (array)$item;
                    }, $chunk->toArray());
                    
                    $pgsql->table($table)->insert($preparedChunk);
                }
                $this->info("✓ {$table} migrada con " . count($data) . " registros.");
            } catch (\Exception $e) {
                $this->error("Error migrando tabla {$table}: " . $e->getMessage());
                return 1;
            }
        }

        } catch (\Exception $e) {
            $this->error("Error fatal: " . $e->getMessage());
            return 1;
        }
        $this->info("Migración completada.");
    }
}
