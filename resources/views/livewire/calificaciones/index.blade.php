<?php

use function Livewire\Volt\{state, computed, mount, updated, layout};
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\User;
use App\Models\Calificacion;
use Flux\Flux;

layout('layouts.app');

state([
    'grupo_id' => '',
    'materia_id' => '',
    'unidad' => '1',
    'notas' => [], // [user_id => calificacion]
    'observaciones' => [], // [user_id => observation]
    'isDirty' => false,
    'esCursoCorto' => false,
]);

$setMasivo = function($valor) {
    if (!$this->grupo_id) return;
    foreach($this->alumnos as $alumno) {
        if (!isset($this->notas[$alumno->id]) || $this->notas[$alumno->id] === '') {
            $this->notas[$alumno->id] = $valor;
        }
    }
    $this->isDirty = true;
};

$limpiarLibreta = function() {
    $this->notas = [];
    $this->observaciones = [];
    $this->loadNotas();
    $this->isDirty = false;
};

$grupos = computed(fn() => Grupo::where('estado', 'activo')->orderBy('nombre')->get());

$materias = computed(function() {
    if (!$this->grupo_id) return [];
    $grupo = Grupo::with(['curso.materias' => function($q) {
        $q->orderBy('pivot_semestre')->orderBy('pivot_orden');
    }])->find($this->grupo_id);
    return $grupo && $grupo->curso ? $grupo->curso->materias : [];
});

$alumnos = computed(function() {
    if (!$this->grupo_id) return [];
    return User::whereHas('roles', fn($q) => $q->where('name', 'alumno'))
        ->whereHas('grupos', fn($q) => $q->where('grupos.id', $this->grupo_id))
        ->orderBy('paterno')
        ->orderBy('materno')
        ->orderBy('nombre')
        ->get();
});

updated(['grupo_id', 'materia_id', 'unidad'], function($value, $key) {
    if ($key === 'grupo_id') {
        $grupo = Grupo::find($value);
        $this->esCursoCorto = $grupo && ($grupo->formato_especial || $grupo->total_horas <= 40);
        
        // Si cambia a curso especial/corto, resetear unidad de ser necesario
        if ($this->esCursoCorto) {
            $this->unidad = 'diagnostica';
        } else {
            $this->unidad = '1';
        }
    }
    $this->loadNotas();
});

$loadNotas = function() {
    if (!$this->grupo_id || !$this->materia_id || !$this->unidad) {
        $this->notas = [];
        $this->observaciones = [];
        return;
    }

    $existing = Calificacion::where('grupo_id', $this->grupo_id)
        ->where('materia_id', $this->materia_id)
        ->where('unidad', $this->unidad)
        ->get();

    $this->notas = $existing->pluck('calificacion', 'user_id')->toArray();
    $this->observaciones = $existing->pluck('observaciones', 'user_id')->toArray();
    
    // Fill empty spots for UI correctly
    foreach($this->alumnos as $alumno) {
        if(!isset($this->notas[$alumno->id])) {
            $this->notas[$alumno->id] = '';
        }
        if(!isset($this->observaciones[$alumno->id])) {
            $this->observaciones[$alumno->id] = '';
        }
    }
};

$guardar = function() {
    $this->validate([
        'grupo_id' => 'required',
        'materia_id' => 'required',
        'unidad' => 'required',
        'notas.*' => 'nullable|numeric|min:0|max:10',
    ]);

    $guardados = 0;
    foreach ($this->notas as $userId => $nota) {
        if ($nota === null || $nota === '') continue;

        Calificacion::updateOrCreate(
            [
                'user_id' => $userId,
                'grupo_id' => $this->grupo_id,
                'materia_id' => $this->materia_id,
                'unidad' => $this->unidad,
            ],
            [
                'calificacion' => $nota,
                'observaciones' => $this->observaciones[$userId] ?? null,
                'registrado_por' => auth()->id(),
            ]
        );
        $guardados++;
    }

    if ($guardados > 0) {
        $this->isDirty = false;
        Flux::toast(heading: 'Acta Registrada', text: "Se guardaron $guardados calificaciones exitosamente.", variant: 'success');
    } else {
        Flux::toast(heading: 'Sin datos', text: 'No ingresaste ninguna calificación para guardar.', variant: 'warning');
    }
};

?>

<div class="p-6">
    <x-slot name="header">Captura de Evaluaciones</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight uppercase">Actas de Calificaciones</h1>
                <p class="text-xs text-zinc-500 font-medium italic">Asignación de puntajes por unidad, materia y grupo de la tira académica.</p>
            </div>
            
            @if($grupo_id && $materia_id && count($this->alumnos) > 0)
                <div class="flex items-center gap-2">
                    @if($isDirty)
                        <flux:badge color="amber" variant="solid" class="animate-pulse">Cambios Pendientes</flux:badge>
                    @endif
                    <flux:button variant="primary" icon="check-circle" wire:click="guardar" class="font-black uppercase tracking-widest text-[10px]">
                        Guardar Libreta
                    </flux:button>
                </div>
            @endif
        </div>

        <!-- Panel Selector Principal -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                <flux:field>
                    <flux:label>Grupo Académico Activo</flux:label>
                    <flux:select wire:model.live="grupo_id" placeholder="-- Escoge un grupo matriculado --" searchable icon="user-group">
                        @foreach ($this->grupos as $grupo)
                            <flux:select.option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->periodo }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Materia u Asignatura</flux:label>
                    <flux:select wire:model.live="materia_id" :disabled="empty($this->materias)" placeholder="{{ empty($this->materias) ? 'Primero selecciona un grupo' : '-- Selecciona la materia --' }}" searchable icon="book-open">
                        @foreach ($this->materias as $materia)
                            <flux:select.option value="{{ $materia->id }}">{{ $materia->nombre }} (Sem {{ $materia->pivot->semestre ?? 'N/A' }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Parcial / Rubro a Evaluar</flux:label>
                    <flux:select wire:model.live="unidad" icon="clipboard-document-check">
                        @if($this->esCursoCorto)
                            <flux:select.option value="diagnostica">Evaluación Diagnóstica</flux:select.option>
                            <flux:select.option value="final">Evaluación Final (Certificación)</flux:select.option>
                        @else
                            <flux:select.option value="1">1ra Unidad / Parcial</flux:select.option>
                            <flux:select.option value="2">2da Unidad / Parcial</flux:select.option>
                            <flux:select.option value="3">3ra Unidad / Parcial</flux:select.option>
                            <flux:select.option value="PROMEDIO_FINAL">Promedio Final del Semestre</flux:select.option>
                            <flux:select.option value="EXTRAORDINARIO">Examen Extraordinario (Global)</flux:select.option>
                        @endif
                    </flux:select>
                </flux:field>
            </div>
        </div>

        @if($grupo_id && $materia_id && count($this->alumnos) > 0)
            <!-- Listado Formulario Calificaciones -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl shadow-sm overflow-hidden overflow-x-auto relative">
                
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20">
                            <flux:icon name="pencil-square" variant="mini" class="text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Cédula de Calificación</h2>
                            <p class="text-[11px] text-zinc-500 font-bold tracking-widest uppercase">
                                Capturando <span class="text-blue-600 dark:text-blue-400">{{ match($unidad) { '1'=>'1ra Unidad', '2'=>'2da Unidad', '3'=>'3ra Unidad', 'PROMEDIO_FINAL'=>'Promedio Final', 'EXTRAORDINARIO'=>'Extraordinario', 'diagnostica' => 'Diagnóstica', 'final' => 'Final', default=>$unidad } }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase text-zinc-400 mr-2">Asignación Rápida:</span>
                        <flux:button variant="ghost" size="sm" class="text-[9px] font-bold" wire:click="setMasivo(10)">Poner 10</flux:button>
                        <flux:button variant="ghost" size="sm" class="text-[9px] font-bold" wire:click="setMasivo(6)">Poner 6</flux:button>
                        <flux:button variant="ghost" size="sm" class="text-[9px] font-bold text-red-500" wire:click="limpiarLibreta">Borrar Todo</flux:button>
                    </div>
                </div>

                <table class="w-full text-left border-collapse whitespace-nowrap" 
                    x-data="{ 
                        focusNext(index) {
                            let next = document.getElementById('nota-' + (index + 1));
                            if (next) next.focus();
                        },
                        focusPrev(index) {
                            let prev = document.getElementById('nota-' + (index - 1));
                            if (prev) prev.focus();
                        }
                    }">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 select-none">Ficha del Cadete / Alumno</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 text-center select-none w-32">Puntaje / Calif.</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-zinc-500 select-none">Anotaciones / Justificación (Opcional)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($this->alumnos as $index => $alumno)
                            <tr wire:key="alm-cal-{{ $alumno->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="size-10 rounded-xl bg-gradient-to-tr from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 font-black text-sm shadow-inner border border-zinc-300 dark:border-zinc-600 uppercase">
                                            {{ substr($alumno->nombre ?? 'A', 0, 1) }}
                                        </div>
                                        <div class="flex flex-col min-w-48 text-wrap leading-tight">
                                            <span class="font-black text-zinc-800 dark:text-white uppercase tracking-tight text-sm">{{ $alumno->nombre_completo }}</span>
                                            <span class="text-[10px] text-zinc-500 italic mt-0.5 tracking-wider font-mono">{{ $alumno->curp ?? 'SIN CURP' }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="relative w-24 mx-auto">
                                        <input type="number" step="0.1" min="0" max="10" 
                                            id="nota-{{ $index }}"
                                            wire:model.live.debounce.500ms="notas.{{ $alumno->id }}" 
                                            @keydown.arrow-down.prevent="focusNext({{ $index }})"
                                            @keydown.arrow-up.prevent="focusPrev({{ $index }})"
                                            @input="$wire.set('isDirty', true)"
                                            class="w-full text-center font-mono font-black text-lg py-2 px-3 bg-white dark:bg-zinc-900 border-2 rounded-xl transition-all focus:outline-none focus:ring-0
                                            {{ floatval($notas[$alumno->id] ?? 0) < 6 && ($notas[$alumno->id] ?? '') !== '' 
                                                ? 'border-red-400 text-red-600 focus:border-red-500 bg-red-50/30' 
                                                : (isset($notas[$alumno->id]) && $notas[$alumno->id] !== '' ? 'border-green-200 dark:border-green-900/50' : 'border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white focus:border-blue-500 hover:border-zinc-300 dark:hover:border-zinc-600') }}" />
                                        
                                        @if(floatval($notas[$alumno->id] ?? 0) < 6 && ($notas[$alumno->id] ?? '') !== '')
                                            <div class="absolute -right-2 -top-2 size-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-sm">
                                                <flux:icon name="exclamation-triangle" variant="micro" />
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <input type="text" wire:model.live.debounce.500ms="observaciones.{{ $alumno->id }}" 
                                        @input="$wire.set('isDirty', true)"
                                        placeholder="Ej. Excelente ensayo, faltó a examen..." 
                                        class="w-full bg-transparent border-0 border-b border-dashed border-zinc-300 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-0 text-xs py-2 px-1 text-zinc-800 dark:text-zinc-200 transition-colors" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <a href="{{ route('calificaciones.acta', ['grupo_id' => $grupo_id, 'materia_id' => $materia_id, 'unidad' => $unidad]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm w-full sm:w-auto">
                        <flux:icon name="document-arrow-down" variant="mini" /> Generar Acta Oficial (PDF)
                    </a>
                    <flux:button variant="primary" icon="check-circle" wire:click="guardar" class="w-full sm:w-auto font-black uppercase tracking-widest text-[11px] px-8">
                        Persistir Evaluaciones
                    </flux:button>
                </div>
            </div>
            
        @elseif($grupo_id && $materia_id && count($this->alumnos) == 0)
            <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/20 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 opacity-80 mt-8">
                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                    <flux:icon name="user-group" class="size-8 text-zinc-400" />
                </div>
                <span class="text-zinc-600 dark:text-zinc-400 font-black uppercase tracking-wider text-sm mb-1">Sin Matrícula Activa</span>
                <span class="text-xs text-zinc-500 font-medium max-w-sm text-center">El grupo seleccionado no posee alumnos para calificar. Inscribe primero a los cadetes en el catálogo de Grupos.</span>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/20 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 opacity-60 mt-8">
                <flux:icon name="academic-cap" class="size-16 text-zinc-300 mb-4" />
                <span class="text-zinc-500 font-black uppercase tracking-wider text-sm">Panel en espera</span>
                <span class="text-xs text-zinc-400 mt-1 max-w-md text-center">Elige un grupo, dictamina una materia y selecciona la unidad a calificar en los selectores de arriba para desplegar la cédula nominal.</span>
            </div>
        @endif
    </div>
</div>
