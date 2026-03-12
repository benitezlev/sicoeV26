<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ConfiguracionInstitucionalController;
use App\Http\Controllers\DocenteController;
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
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\AsistenciaController;


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
    //Route::get('/configuracion', App\Livewire\ConfiguracionInstitucionalForm::class)->name('config.index');
    Route::get('config', [ConfiguracionInstitucionalController::class, 'index'])->name('config.index');
    Route::get('plantel', [PlantelController::class, 'index'])->name('plantel.index');
    Route::get('plantel/create', [PlantelController::class, 'create'])->name('plantel.create');
    Route::get('/admin/alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
    Route::get('/admin/alumnos/importar', [AlumnoController::class, 'vistaImportar'])->name('vistaImport');
    Route::post('/admin/alumnos/importar', [AlumnoController::class, 'importar'])->name('alumnos.importar');

    Route::middleware(['auth'])->group(function () {
    // Rutas Docentes
    Route::get('/docente', [ProfesorController::class, 'index'])->name('profesores');
    Route::get('/docente/create', [ProfesorController::class, 'create'])->name('profesores.create');
    Route::get('/docente/show/{docente}', [ProfesorController::class, 'show'])->name('profesores.show');
    Route::get('/docente/edit/{docente}', [ProfesorController::class, 'edit'])->name('profesores.edit');
    });


    //exportar errores duplicados en alumnos
    Route::post('/alumnos/duplicados/exportar', [AlumnoController::class, 'exportarDuplicados'])
    ->name('alumnos.exportar.duplicados');
    // Exportar errores
    Route::post('/alumnos/errores/exportar', [AlumnoController::class, 'exportarErrores'])
    ->name('alumnos.exportar.errores');

    //importaciones

     // Adminisracion de Roles
    Route::get('/roles', [RolController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RolController::class, 'create'])->name('roles.create');

    // rutas expedientes

    Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
    Route::get('/expedientes/{id}', [ExpedienteController::class, 'show'])->name('expedientes.show');

    Route::get('/expedientes/{id}/documentos/cargar', [DocumentoExpedienteController::class, 'formularioCarga'])->name('documentos.cargar');
    Route::post('/expedientes/{id}/documentos/cargar', [DocumentoExpedienteController::class, 'cargar'])->name('documentos.store');
    Route::post('/expedientes/{id}/validar', [ExpedienteController::class, 'revalidar'])->name('expedientes.validar');

    // rutas documentos expediente

    Route::get('/documentos/{id}/validar', [DocumentoExpedienteController::class, 'validar'])->name('documentos.validar');
    Route::get('/documentos/{id}/observar', [DocumentoExpedienteController::class, 'formularioObservacion'])->name('documentos.observar');
    Route::post('/documentos/{id}/observar', [DocumentoExpedienteController::class, 'registrarObservacion'])->name('documentos.observacion.store');
});

 Route::middleware(['auth', 'role:admin_ti|coordinador'])->group(function () {
 Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');


    });

Route::get('/grupos/{id}/exportar', [ExportacionesController::class, 'exportarGrupo'])
    ->middleware(['auth', 'role:admin_ti|coordinador'])
    ->name('grupos.exportar');


Route::middleware(['auth', 'role:admin_ti|coordinador'])->prefix('cursos')->group(function () {

    // Listar todos los cursos
    Route::get('/', [CursoController::class, 'index'])->name('cursos.index');

    // Mostrar formulario de creación
    Route::get('/crear', [CursoController::class, 'create'])->name('cursos.create');

    // Guardar nuevo curso
    Route::post('/', [CursoController::class, 'store'])->name('cursos.store');

    // Mostrar curso específico
    Route::get('/{curso}', [CursoController::class, 'show'])->name('cursos.show');

    // Editar curso
    Route::get('/{curso}/editar', [CursoController::class, 'edit'])->name('cursos.edit');

    // Actualizar curso
    Route::put('/{curso}', [CursoController::class, 'update'])->name('cursos.update');

    // Eliminar curso
    Route::delete('/{curso}', [CursoController::class, 'destroy'])->name('cursos.destroy');

    // Exportar listado de cursos (opcional)
    Route::get('/exportar/pdf', [CursoController::class, 'exportarPDF'])->name('cursos.exportar.pdf');
});

// Panel principal: listado de cursos y materias
Route::get('/panel/materias', [PanelMateriasController::class, 'index'])
    ->name('panel.materias');

// Editar materias de un curso (vista de edición inline)
Route::get('/panel/materias/{curso}/edit', [PanelMateriasController::class, 'edit'])
    ->name('panel.materias.edit');

// Actualizar materias de un curso (guardar cambios)
Route::put('/panel/materias/{curso}', [PanelMateriasController::class, 'update'])
    ->name('panel.materias.update');

// Agregar nueva materia a un curso
Route::get('/panel/materias/{curso}/add', [PanelMateriasController::class, 'create'])
    ->name('panel.materias.add');
Route::post('/panel/materias/{curso}', [PanelMateriasController::class, 'store'])
    ->name('panel.materias.store');

// Eliminar materia de un curso
Route::delete('/panel/materias/{curso}/{materia}', [PanelMateriasController::class, 'destroy'])
    ->name('panel.materias.remove');

Route::get('/panel/materias/{curso}/export/pdf', [PanelMateriasController::class, 'exportPdf'])
    ->name('panel.materias.export.pdf');

Route::get('/panel/materias/{curso}/export/excel', [PanelMateriasController::class, 'exportExcel'])
    ->name('panel.materias.export.excel');


    /// Materias

// Listado de materias
Route::get('/materias', [MateriaController::class, 'index'])->name('materias.index');

// Formulario de creación
Route::get('/materias/create', [MateriaController::class, 'create'])->name('materias.create');

// Guardar nueva materia
Route::post('/materias', [MateriaController::class, 'store'])->name('materias.store');

// Formulario de edición
Route::get('/materias/{materia}/edit', [MateriaController::class, 'edit'])->name('materias.edit');

// Actualizar materia
Route::put('/materias/{materia}', [MateriaController::class, 'update'])->name('materias.update');

// Eliminar materia
Route::delete('/materias/{materia}', [MateriaController::class, 'destroy'])->name('materias.destroy');


// grupos

// Listado de grupos
Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');

// Crear grupo
Route::get('/grupos/create', [GrupoController::class, 'create'])->name('grupos.create');
Route::post('/grupos', [GrupoController::class, 'store'])->name('grupos.store');

// Ver detalle de grupo
Route::get('/grupos/{grupo}', [GrupoController::class, 'show'])->name('grupos.show');

// Editar grupo
Route::get('/grupos/{grupo}/edit', [GrupoController::class, 'edit'])->name('grupos.edit');
Route::put('/grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');

// Asignar docente vía API
Route::post('/grupos/{grupo}/docente', [GrupoController::class, 'asignarDocente'])->name('grupos.asignarDocente');

// Asignar alumnos
Route::post('/grupos/{grupo}/alumnos', [GrupoController::class, 'asignarAlumnos'])->name('grupos.asignarAlumnos');

// Subir expediente
Route::post('/grupos/{grupo}/expediente', [GrupoController::class, 'subirExpediente'])->name('grupos.subirExpediente');

// Métricas del grupo
Route::get('/grupos/{grupo}/metricas', [GrupoController::class, 'metricas'])->name('grupos.metricas');


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
