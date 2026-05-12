<?php

use function Livewire\Volt\{state, computed, mount};
use App\Services\OllamaService;
use App\Models\ConfiguracionInstitucional;
use Flux\Flux;

state([
    'messages' => [
        [
            'role' => 'assistant',
            'text' => '¡Hola! Soy **SICOE-IA**, tu copiloto de analítica local. Puedo generar y ejecutar estadísticas avanzadas de matrícula, géneros, planteles y recursos directamente desde nuestra base de datos.',
            'sql' => null,
            'data' => null,
            'success' => true
        ]
    ],
    'input' => '',
    'loading' => false,
    'activoGlobal' => true,
]);

mount(function () {
    $this->activoGlobal = ConfiguracionInstitucional::isCopilotoActivo();
});

$toggleGlobal = function () {
    if (!auth()->user()->hasRole('superadmin')) {
        return;
    }

    $this->activoGlobal = !$this->activoGlobal;
    ConfiguracionInstitucional::setCopilotoActivo($this->activoGlobal);

    Flux::toast(
        heading: $this->activoGlobal ? 'Copiloto IA Activado' : 'Copiloto IA Desactivado',
        text: $this->activoGlobal 
            ? 'El servicio conversacional local se ha habilitado para todo el personal.'
            : 'El servicio conversacional se ha deshabilitado para Control Escolar y operadores.',
        variant: 'info'
    );
};

$ask = function (OllamaService $ollama) {
    // Si no está activo globalmente y no es superadmin, no permitir consultas
    if (!$this->activoGlobal && !auth()->user()->hasRole('superadmin')) {
        return;
    }

    if (empty(trim($this->input))) {
        return;
    }

    $userQuestion = trim($this->input);
    
    $this->messages[] = [
        'role' => 'user',
        'text' => $userQuestion,
        'sql' => null,
        'data' => null,
        'success' => true
    ];

    $this->input = '';
    
    try {
        $schema = $ollama->getSicoeSchemaPrompt();
        $rawResponse = $ollama->generate($userQuestion, $schema);

        if (!$rawResponse) {
            $this->messages[] = [
                'role' => 'assistant',
                'text' => 'Disculpa, no logré comunicarme con el servidor local de Inteligencia Artificial (Ollama) en la IP 192.168.3.4. Por favor, verifica que el servicio esté encendido.',
                'sql' => null,
                'data' => null,
                'success' => false
            ];
            return;
        }

        $queryResult = $ollama->executeSecureQuery($rawResponse);

        if (!$queryResult['success']) {
            $this->messages[] = [
                'role' => 'assistant',
                'text' => $queryResult['message'],
                'sql' => $queryResult['sql'],
                'data' => null,
                'success' => false
            ];
            return;
        }

        $rowsCount = count($queryResult['rows']);
        $sqlUsed = $queryResult['sql'];
        $dataResult = $queryResult['rows'];

        if ($rowsCount === 0) {
            $assistantText = "Ejecuté la consulta estadística en la base de datos local de forma segura, pero **no se encontraron registros** que coincidan con los criterios especificados.";
        } else {
            $assistantText = "¡Excelente! He ejecutado la analítica con éxito en nuestra base de datos. Se obtuvieron **{$rowsCount} fila(s)** de resultados:";
        }

        $this->messages[] = [
            'role' => 'assistant',
            'text' => $assistantText,
            'sql' => $sqlUsed,
            'data' => $dataResult,
            'success' => true
        ];

    } catch (\Exception $e) {
        $this->messages[] = [
            'role' => 'assistant',
            'text' => 'Ocurrió un error inesperado al procesar la analítica: ' . $e->getMessage(),
            'sql' => null,
            'data' => null,
            'success' => false
        ];
    }
};

$clearChat = function () {
    $this->messages = [
        [
            'role' => 'assistant',
            'text' => '¡Hola! Soy **SICOE-IA**, tu copiloto de analítica local. Puedo generar y ejecutar estadísticas avanzadas de matrícula, géneros, planteles y recursos directamente desde nuestra base de datos.',
            'sql' => null,
            'data' => null,
            'success' => true
        ]
    ];
};

?>

<div class="bg-white dark:bg-zinc-800 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col h-[550px]" wire:key="sicoe-copiloto-container">
    <!-- Encabezado del Copiloto -->
    <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-700 pb-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 relative">
                <flux:icon name="cpu-chip" class="w-5 h-5 animate-pulse" />
                @if ($activoGlobal)
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-zinc-800 rounded-full"></span>
                @else
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-zinc-400 border-2 border-white dark:border-zinc-800 rounded-full"></span>
                @endif
            </div>
            <div>
                <flux:heading size="lg" class="text-zinc-900 dark:text-white uppercase tracking-tight font-black flex items-center gap-2">
                    SICOE Copiloto IA 
                    @if ($activoGlobal)
                        <flux:badge size="sm" color="emerald" variant="solid" class="text-[8px] tracking-widest font-black">LOCAL</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc" variant="solid" class="text-[8px] tracking-widest font-black bg-zinc-400">DESACTIVADO</flux:badge>
                    @endif
                </flux:heading>
                <p class="text-[10px] text-zinc-500 font-medium">Conectado a Ollama (192.168.3.4) • Qwen 2.5 Coder 7B</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Interruptor para el Administrador General -->
            @if (auth()->user()->hasRole('superadmin'))
                <div class="flex items-center gap-1.5 border-r border-zinc-100 dark:border-zinc-700 pr-3">
                    <span class="text-[9px] text-zinc-400 font-black uppercase tracking-wider">Acceso IA:</span>
                    <button type="button" wire:click="toggleGlobal" class="outline-none focus:outline-none">
                        @if ($activoGlobal)
                            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 text-[8px] font-black uppercase rounded-full border border-emerald-200 dark:border-emerald-900/20">Habilitado</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 bg-zinc-100 text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400 text-[8px] font-black uppercase rounded-full border border-zinc-200 dark:border-zinc-700">Desactivado</span>
                        @endif
                    </button>
                </div>
            @endif
            
            @if ($activoGlobal || auth()->user()->hasRole('superadmin'))
                <flux:button variant="ghost" size="xs" icon="arrow-path" wire:click="clearChat" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" title="Reiniciar chat">Limpiar</flux:button>
            @endif
        </div>
    </div>

    <!-- Validar Disponibilidad de Uso -->
    @if ($activoGlobal || auth()->user()->hasRole('superadmin'))
        
        <!-- Si está apagado globalmente pero soy superadmin, mostrar advertencia de simulación -->
        @if (!$activoGlobal && auth()->user()->hasRole('superadmin'))
            <div class="mb-4 p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-2">
                <flux:icon name="exclamation-triangle" class="w-4 h-4 text-amber-500 flex-shrink-0 animate-bounce" />
                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-tight">Estatus: Modo de Simulación Administrativa (Desactivado para personal operativo)</span>
            </div>
        @endif

        <!-- Contenedor del Historial de Mensajes -->
        <div class="flex-1 overflow-y-auto space-y-4 pr-2 mb-4 scrollbar-thin" x-ref="chatContainer" x-init="$watch('messages', () => $nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight }))">
            @foreach ($messages as $msg)
                <div class="flex flex-col {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }} gap-1">
                    <!-- Nombre y Rol -->
                    <span class="text-[9px] text-zinc-400 font-bold uppercase tracking-wider px-2">
                        {{ $msg['role'] === 'user' ? 'Tú (Operador)' : 'SICOE-IA' }}
                    </span>

                    <!-- Burbuja de Mensaje -->
                    <div class="max-w-[85%] rounded-2xl p-4 text-xs leading-relaxed {{ $msg['role'] === 'user' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-100 dark:border-zinc-700 rounded-tl-none' }}">
                        <p class="font-medium whitespace-pre-line">{{ $msg['text'] }}</p>

                        <!-- Tabla de Resultados Dinámica -->
                        @if (!empty($msg['data']))
                            <div class="mt-4 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden overflow-x-auto shadow-sm max-w-full">
                                <table class="w-full text-left border-collapse text-[10px]">
                                    <thead>
                                        <tr class="bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                                            @foreach (array_keys($msg['data'][0]) as $column)
                                                <th class="px-3 py-2 font-black uppercase text-zinc-500 dark:text-zinc-400">{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900/30">
                                        @foreach ($msg['data'] as $row)
                                            <tr>
                                                @foreach ($row as $value)
                                                    <td class="px-3 py-1.5 font-bold text-zinc-900 dark:text-zinc-100">
                                                        {{ is_bool($value) ? ($value ? 'Activo' : 'Inactivo') : $value }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- Consulta SQL Utilizada -->
                        @if (!empty($msg['sql']))
                            <div class="mt-3">
                                <details class="group bg-zinc-100/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
                                    <summary class="flex justify-between items-center px-3 py-2 text-[9px] font-black uppercase tracking-wider text-zinc-500 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 select-none outline-none">
                                        <span>Consulta SQL Ejecutada</span>
                                        <flux:icon name="chevron-down" class="w-3 h-3 transition-transform group-open:rotate-180" />
                                    </summary>
                                    <div class="px-3 pb-3 pt-1 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                                        <pre class="font-mono text-[9px] text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap break-all bg-zinc-900 text-emerald-400 p-2.5 rounded-lg border border-zinc-800 select-all">{{ $msg['sql'] }}</pre>
                                    </div>
                                </details>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Burbuja de Carga con Animación -->
            <div wire:loading wire:target="ask" class="flex flex-col items-start gap-1">
                <span class="text-[9px] text-zinc-400 font-bold uppercase tracking-wider px-2">SICOE-IA</span>
                <div class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-100 dark:border-zinc-700 rounded-2xl rounded-tl-none p-4 max-w-[85%] flex items-center gap-3">
                    <div class="flex gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.3s]"></span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.15s]"></span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce"></span>
                    </div>
                    <span class="text-xs font-bold text-zinc-500 animate-pulse italic">Consultando analítica con Ollama...</span>
                </div>
            </div>
        </div>

        <!-- Entrada del Operador (Formulario de Envío) -->
        <form wire:submit="ask" class="flex gap-2 items-center border-t border-zinc-100 dark:border-zinc-700 pt-3" wire:loading.attr="disabled" wire:target="ask">
            <div class="flex-1">
                <flux:input wire:model="input" placeholder="Pregunta a la IA (ej: ¿Cuántos hombres y mujeres tenemos capacitados?)..." class="font-medium" required autocomplete="off" />
            </div>
            <flux:button type="submit" variant="primary" class="font-black uppercase tracking-wider text-[10px] px-4 py-2 flex items-center gap-1" wire:loading.attr="disabled" wire:target="ask">
                <span>Consultar</span>
                <flux:icon name="paper-airplane" variant="mini" />
            </flux:button>
        </form>

    @else
        <!-- ================= VISTA DE ESTADO DESACTIVADO (PÚBLICO) ================= -->
        <div class="flex-1 flex flex-col items-center justify-center text-center p-8 space-y-6">
            <div class="w-16 h-16 rounded-3xl bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center text-zinc-400 dark:text-zinc-600 border border-zinc-200 dark:border-zinc-700 shadow-inner">
                <flux:icon name="cpu-chip" class="w-8 h-8" />
            </div>
            <div class="space-y-2 max-w-sm">
                <h3 class="text-lg font-black text-zinc-900 dark:text-white uppercase tracking-tight">Copiloto IA Suspendido</h3>
                <p class="text-xs text-zinc-500 leading-relaxed font-medium">
                    El servicio de análisis conversacional de Inteligencia Artificial se encuentra desactivado temporalmente por disposición de la **Dirección de Control Escolar**.
                </p>
            </div>
            <flux:badge size="sm" color="zinc" variant="solid" class="bg-zinc-500 text-[9px] tracking-widest font-black uppercase">Servicio Inactivo</flux:badge>
        </div>
    @endif
</div>
