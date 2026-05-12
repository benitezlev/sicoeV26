<?php

declare(strict_types=1);

use App\Models\MetaCapacitacion;

it('can persist a training goal for a specific fiscal year', function () {
    $meta = MetaCapacitacion::create([
        'anio' => 2027,
        'meta' => 5000,
    ]);

    expect($meta->id)->not->toBeNull()
        ->and($meta->anio)->toBe(2027)
        ->and($meta->meta)->toBe(5000);
});

it('prevents duplicate goals for the same fiscal cycle', function () {
    MetaCapacitacion::create([
        'anio' => 2028,
        'meta' => 4000,
    ]);

    // Intentar insertar duplicado debe lanzar QueryException debido al constraint de unicidad
    expect(fn() => MetaCapacitacion::create([
        'anio' => 2028,
        'meta' => 4500,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
