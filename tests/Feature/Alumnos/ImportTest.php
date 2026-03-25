<?php

use App\Models\User;
use App\Models\Grupo;
use Livewire\Volt\Volt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Fake storage disks
    Storage::fake('public');
    Storage::fake('local');
    Storage::fake('archivo_importacion');

    // Ensure 'alumno' role exists for tests
    Role::firstOrCreate(['name' => 'alumno']);
    Role::firstOrCreate(['name' => 'admin']);
    
    // Create prerequisites for Grupo
    DB::table('planteles')->updateOrInsert(['id' => 1], ['name' => 'PLANTEL TEST']);
    DB::table('cursos')->updateOrInsert(['id' => 1], [
        'nombre' => 'CURSO TEST',
        'identificador' => 'TEST-01',
        'tipo' => 'FORMACION',
        'num_horas' => 40,
        'categoria' => 'BASICA'
    ]);

    // Create a user to act as the importer
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('imports activo users by CUIP', function () {
    // Create existing user with CUIP
    $user = User::create([
        'nombre' => 'EXISTENTE',
        'paterno' => 'GORE',
        'materno' => 'VALKYRIE',
        'curp' => 'CURP_OLD_88',
        'cuip' => 'CUIP_KEY_88',
        'username' => 'existing.user',
        'email' => 'existing@sicoe.mx',
        'password' => 'secret',
    ]);

    $csvContent = "cuip,curp,cup,paterno,materno,nombre,dependencia,adscripcion,perfil,sexo,tipo,username,password,grupo\n";
    $csvContent .= "CUIP_KEY_88,CURP_NEW_99,CUP-1,GARCIA,,JUAN,SS,ZONA,POLICIA,HOMBRE,activo,juan,123,\n";

    $file = UploadedFile::fake()->createWithContent('alumnos.csv', $csvContent);

    $this->actingAs($this->admin);

    Volt::test('usuarios.import')
        ->set('archivo', $file)
        ->call('importar');

    // Should find the SAME user because CUIP matched
    $foundUser = User::where('cuip', 'CUIP_KEY_88')->first();
    expect($foundUser->id)->toBe($user->id);
    expect($foundUser->curp)->toBe('CURP_OLD_88'); 
});

it('imports aspirante users by CURP', function () {
    $csvContent = "cuip,curp,cup,paterno,materno,nombre,dependencia,adscripcion,perfil,sexo,tipo,username,password,grupo\n";
    $csvContent .= ",CURP_ASP_123,,RUIZ,,MARIA,UMS,ZONA,CADETE,MUJER,aspirante,maria,123,\n";

    $file = UploadedFile::fake()->createWithContent('alumnos.csv', $csvContent);

    $this->actingAs($this->admin);

    Volt::test('usuarios.import')
        ->set('archivo', $file)
        ->call('importar');

    expect(User::where('curp', 'CURP_ASP_123')->exists())->toBeTrue();
    $user = User::where('curp', 'CURP_ASP_123')->first();
    expect($user->hasRole('alumno'))->toBeTrue();
});

it('enrolls users in a group by name', function () {
    $grupo = Grupo::create([
        'nombre' => 'PFA-NEZ-GPO01',
        'plantel_id' => 1,
        'curso_id' => 1,
        'periodo' => '2025'
    ]);
    
    $csvContent = "cuip,curp,cup,paterno,materno,nombre,dependencia,adscripcion,perfil,sexo,tipo,username,password,grupo\n";
    $csvContent .= ",CURP_AUTO_GRP,,LARA,,JOSE,SS,ZONA,POLICIA,HOMBRE,aspirante,jose,123,PFA-NEZ-GPO01\n";

    $file = UploadedFile::fake()->createWithContent('alumnos.csv', $csvContent);

    $this->actingAs($this->admin);

    Volt::test('usuarios.import')
        ->set('archivo', $file)
        ->call('importar');

    $user = User::where('curp', 'CURP_AUTO_GRP')->first();
    expect($user->grupos()->where('grupo_id', $grupo->id)->exists())->toBeTrue();
});
