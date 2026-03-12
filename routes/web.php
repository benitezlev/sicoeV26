<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ConfiguracionInstitucionalController;
use App\Http\Controllers\PlantelController;
use App\Livewire\AlumnoImport;
use Illuminate\Support\Facades\Route;
use App\Livewire\ConfiguracionInstitucionalForm;
use Spatie\Permission\Exceptions\Role as role;
use App\Http\Controllers\ExportacionesController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\DocumentoExpedienteController;
use App\Http\Controllers\PanelMateriasController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\GrupoController;
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

Route::middleware(['auth', 'role:admin_ti'])->prefix('admin')->group(function () {
    Volt::route('config', 'configuracion-institucional')->name('config.index');
    Volt::route('plantel', 'planteles.index')->name('plantel.index');
    Volt::route('/usuarios', 'usuarios.index')->name('alumnos.index');
    Volt::route('/alumnos/importar', 'usuarios.import')->name('alumnos.importar');

    // Rutas Docentes
    Volt::route('/docente', 'docentes.index')->name('profesores');


    //exportar errores duplicados en alumnos
    Route::post('/alumnos/duplicados/exportar', [AlumnoController::class, 'exportarDuplicados'])
    ->name('alumnos.exportar.duplicados');
    // Exportar errores
    Route::post('/alumnos/errores/exportar', [AlumnoController::class, 'exportarErrores'])
    ->name('alumnos.exportar.errores');

    //importaciones

     // Adminisracion de Roles
    Volt::route('/roles', 'roles.index')->name('roles');

    // rutas expedientes
    Volt::route('/expedientes', 'expedientes.index')->name('expedientes.index');
    Volt::route('/expedientes/{expediente}', 'expedientes.show')->name('expedientes.show');
});

 Route::middleware(['auth', 'role:admin_ti|coordinador'])->group(function () {
 Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');


    });

Route::get('/grupos/{id}/exportar', [ExportacionesController::class, 'exportarGrupo'])
    ->middleware(['auth', 'role:admin_ti|coordinador'])
    ->name('grupos.exportar');


Route::middleware(['auth', 'role:admin_ti|coordinador'])->group(function () {
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
Route::middleware(['auth', 'role:admin_ti|coordinador'])->group(function () {
    Volt::route('/grupos', 'grupos.index')->name('grupos.index');
    Volt::route('/grupos/{grupo}', 'grupos.show')->name('grupos.show');
    
    // Métricas del grupo (Mantener controller si tiene lógica compleja de vista, o migrar luego)
    Route::get('/grupos/{grupo}/metricas', [GrupoController::class, 'metricas'])->name('grupos.metricas');
});


// Grupo de rutas para asistencias
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
