<?php

use App\Http\Controllers\ImportacionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportacionesController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PanelMateriasController;
use App\Http\Controllers\AsistenciaController;
use Livewire\Volt\Volt;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'role:superadmin|admin_ti|control_escolar'])->prefix('admin')->group(function () {
    Volt::route('config', 'configuracion-institucional')->name('config.index');
    Volt::route('plantel', 'planteles.index')->name('plantel.index');
    Volt::route('/usuarios', 'usuarios.index')->name('alumnos.index');
    Volt::route('/alumnos/importar', 'usuarios.import')->name('alumnos.importar');
    Volt::route('/expedientes/importar-zip', 'expedientes.import-zip')->name('expedientes.import-zip');
    Volt::route('/usuarios/carga-masiva', 'usuarios.bulk-documents')->name('usuarios.carga-masiva');

    // Rutas Docentes
    Volt::route('/docente', 'docentes.index')->name('profesores');


    //importaciones

     // Adminisracion de Roles
    Volt::route('/roles', 'roles.index')->name('roles');

    // rutas expedientes
    Volt::route('/expedientes', 'expedientes.index')->name('expedientes.index');
    Volt::route('/expedientes/{expediente}', 'expedientes.show')->name('expedientes.show');
    
    // Calificaciones (Movido aquí para mayor visibilidad del sistema)
    Volt::route('/calificaciones', 'calificaciones.index')->name('calificaciones.index');
    Route::get('/calificaciones/acta', [ExportacionesController::class, 'exportarActa'])->name('calificaciones.acta');
});

 Route::middleware(['auth', 'role:superadmin|admin_ti|control_escolar'])->group(function () {
 Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');


    });

Route::get('/grupos/{id}/exportar', [ExportacionesController::class, 'exportarGrupo'])
    ->middleware(['auth', 'role:superadmin|admin_ti|control_escolar|operador'])
    ->name('grupos.exportar');


Route::middleware(['auth', 'role:superadmin|admin_ti|control_escolar'])->group(function () {
    // Cursos
    Volt::route('/cursos', 'cursos.index')->name('cursos.index');
    Route::get('/cursos/exportar/pdf', [CursoController::class, 'exportarPDF'])->name('cursos.exportar.pdf');

    // Materias
    Volt::route('/materias', 'materias.index')->name('materias.index');

    // Panel Materias (Asignación)
    Volt::route('/panel/materias', 'cursos.materias')->name('panel.materias');
    Route::get('/panel/materias/{curso}/export/pdf', [PanelMateriasController::class, 'exportPdf'])->name('panel.materias.export.pdf');
    Route::get('/panel/materias/{curso}/export/excel', [PanelMateriasController::class, 'exportExcel'])->name('panel.materias.export.excel');
});



// grupos
Route::middleware(['auth', 'role:superadmin|admin_ti|control_escolar|operador'])->group(function () {
    Volt::route('/grupos', 'grupos.index')->name('grupos.index');
    Volt::route('/grupos/{grupo}', 'grupos.show')->name('grupos.show');
    
    // Métricas del grupo en Volt
    Volt::route('/grupos/{grupo}/metricas', 'grupos.metricas')->name('grupos.metricas');
});


// Grupo de rutas para asistencias
Route::middleware(['auth', 'role:superadmin|admin_ti|control_escolar|operador'])->group(function () {
    Volt::route('/asistencias/dashboard', 'asistencias.index')->name('asistencias.index');
    Volt::route('/asistencias/pase-lista', 'asistencias.pase-lista')->name('asistencias.pase-lista');

    Route::prefix('asistencias')->group(function () {
        // Generar lista en PDF (para impresión)
        Route::get('/generar/{grupo}', [AsistenciaController::class, 'generarLista'])
            ->name('asistencias.generar');

        // Subir lista escaneada
        Route::post('/subir/{grupo}', [AsistenciaController::class, 'subirLista'])
            ->name('asistencias.subir');

        // Validar lista subida
        Route::post('/validar/{id}', [AsistenciaController::class, 'validarLista'])
            ->name('asistencias.validar');
    });
});
