<?php

declare(strict_types=1);

use App\Models\Recurso;
use App\Models\Grupo;
use App\Models\Plantel;
use App\Models\Curso;

it('can create a resource with valid properties', function () {
    $recurso = Recurso::create([
        'nombre' => 'Fondo de Prueba',
        'clave' => 'PRUEBA-01',
        'descripcion' => 'Fondo de prueba para cobertura de test.',
        'activo' => true,
    ]);

    expect($recurso->id)->not->toBeNull()
        ->and($recurso->nombre)->toBe('Fondo de Prueba')
        ->and($recurso->clave)->toBe('PRUEBA-01')
        ->and($recurso->activo)->toBeTrue();
});

it('can associate a resource to a group', function () {
    // 1. Crear el Recurso
    $recurso = Recurso::create([
        'nombre' => 'FASP Test',
        'clave' => 'FASP-TEST',
        'activo' => true,
    ]);

    // 2. Crear dependencias para Grupo
    $plantel = Plantel::create([
        'name' => 'PLANTEL TEST',
    ]);

    $curso = Curso::create([
        'identificador' => 'CCC-01',
        'nombre' => 'Curso de Control de Confianza',
        'tipo' => 'especialización',
        'num_horas' => 40,
        'categoria' => 'Policial',
    ]);

    // 3. Crear el Grupo asignando el recurso
    $grupo = Grupo::create([
        'nombre' => 'Grupo de Test A',
        'plantel_id' => $plantel->id,
        'curso_id' => $curso->id,
        'recurso_id' => $recurso->id,
        'periodo' => '2026-I',
        'estado' => 'activo',
    ]);

    // 4. Assertions de relación
    expect($grupo->recurso_id)->toBe($recurso->id)
        ->and($grupo->recurso->nombre)->toBe('FASP Test')
        ->and($recurso->grupos)->toHaveCount(1)
        ->and($recurso->grupos->first()->id)->toBe($grupo->id);
});
