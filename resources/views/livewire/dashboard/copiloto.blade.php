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

<div x-data="{ open: false }" class="relative" wire:key="sicoe-copiloto-global">
    <!-- Botón Flotante de Chat (FAB) -->
    <button 
        @click="open = !open" 
        type="button"
        class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-gradient-to-tr from-indigo-600 to-violet-700 text-white rounded-full shadow-lg shadow-indigo-600/30 hover:scale-110 active:scale-95 transition-all duration-300 focus:outline-none group border border-indigo-500/10"
        title="Consultar Copiloto IA de SICOE"
    >
        <!-- Icono de Cerebro/Chip con efecto de pulso -->
        <div class="relative">
            <flux:icon name="cpu-chip" class="w-6 h-6 group-hover:rotate-12 transition-transform" />
            <span class="absolute -top-1 -right-1 flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $activoGlobal ? 'bg-emerald-400' : 'bg-zinc-400' }}"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 {{ $activoGlobal ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
            </span>
        </div>
    </button>

    <!-- Ventana Flotante del Chat -->
    <div 
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-10 scale-95"
        class="fixed bottom-24 right-6 w-[440px] max-w-[calc(100vw-2rem)] h-[620px] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[28px] shadow-2xl z-50 overflow-hidden flex flex-col"
        @click.away="open = false"
    >
        <!-- Encabezado del Copiloto -->
        <div class="flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200/60 dark:border-zinc-800/80 px-5 py-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 relative">
                    <flux:icon name="cpu-chip" class="w-4 h-4 animate-pulse" />
                    @if ($activoGlobal)
                        <span class="absolute bottom-0 right-0 w-2 h-2 bg-emerald-500 border-2 border-zinc-50 dark:border-zinc-900 rounded-full"></span>
                    @else
                        <span class="absolute bottom-0 right-0 w-2 h-2 bg-zinc-400 border-2 border-zinc-50 dark:border-zinc-900 rounded-full"></span>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-tight flex items-center gap-1.5">
                        <span>SICOE COPILOTO IA</span>
                        @if ($activoGlobal)
                            <span class="inline-block px-1.5 py-0.5 bg-emerald-500 text-white text-[7px] font-black tracking-widest rounded uppercase">LOCAL</span>
                        @else
                            <span class="inline-block px-1.5 py-0.5 bg-zinc-400 text-white text-[7px] font-black tracking-widest rounded uppercase">INACTIVO</span>
                        @endif
                    </div>
                    <p class="text-[8px] text-zinc-400 font-bold uppercase tracking-wider">Ollama local @ 192.168.3.4 (Llama 3)</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Interruptor para el Administrador General -->
                @if (auth()->user()->hasRole('superadmin'))
                    <button type="button" wire:click="toggleGlobal" class="outline-none focus:outline-none transition-all duration-200 active:scale-95" title="Activar/Desactivar IA globalmente">
                        @if ($activoGlobal)
                            <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 text-[8px] font-black uppercase rounded-full border border-emerald-200 dark:border-emerald-900/20">Activo</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 bg-zinc-100 text-zinc-600 dark:bg-zinc-950/20 dark:text-zinc-400 text-[8px] font-black uppercase rounded-full border border-zinc-200 dark:border-zinc-800">Inactivo</span>
                        @endif
                    </button>
                @endif
                
                @if ($activoGlobal || auth()->user()->hasRole('superadmin'))
                    <flux:button variant="ghost" size="xs" icon="arrow-path" wire:click="clearChat" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" title="Reiniciar chat" />
                @endif

                <flux:button variant="ghost" size="xs" icon="x-mark" @click="open = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" />
            </div>
        </div>

        <!-- Validar Disponibilidad de Uso -->
        @if ($activoGlobal || auth()->user()->hasRole('superadmin'))
            
            <!-- Si está apagado globalmente pero soy superadmin, mostrar advertencia de simulación -->
            @if (!$activoGlobal && auth()->user()->hasRole('superadmin'))
                <div class="mx-5 mt-4 p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center gap-2 flex-shrink-0">
                    <flux:icon name="exclamation-triangle" class="w-3.5 h-3.5 text-amber-500 flex-shrink-0 animate-bounce" />
                    <span class="text-[9px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-tight">Modo de Simulación (Inactivo para operadores)</span>
                </div>
            @endif

            <!-- Contenedor del Historial de Mensajes -->
            <div class="flex-1 overflow-y-auto space-y-4 px-5 py-4 scrollbar-thin" x-ref="chatContainer" x-init="$watch('messages', () => $nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight }))">
                @foreach ($messages as $msg)
                    <div class="flex flex-col {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }} gap-1">
                        <!-- Nombre y Rol -->
                        <span class="text-[8px] text-zinc-400 font-bold uppercase tracking-wider px-1">
                            {{ $msg['role'] === 'user' ? 'Tú (Operador)' : 'SICOE-IA' }}
                        </span>

                        <!-- Burbuja de Mensaje -->
                        <div class="max-w-[90%] rounded-2xl p-3 text-[11px] leading-relaxed {{ $msg['role'] === 'user' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-zinc-50 dark:bg-zinc-900/40 text-zinc-800 dark:text-zinc-200 border border-zinc-200/50 dark:border-zinc-800 rounded-tl-none' }}">
                            <p class="font-medium whitespace-pre-line">{{ $msg['text'] }}</p>

                            <!-- Tabla de Resultados Dinámica -->
                            @if (!empty($msg['data']))
                                <div class="mt-3 border border-zinc-200/60 dark:border-zinc-800 rounded-xl overflow-hidden overflow-x-auto shadow-sm max-w-full">
                                    <table class="w-full text-left border-collapse text-[9px]">
                                        <thead>
                                            <tr class="bg-zinc-100 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800">
                                                @foreach (array_keys($msg['data'][0]) as $column)
                                                    <th class="px-2.5 py-1.5 font-black uppercase text-zinc-500 dark:text-zinc-400">{{ $column }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800 bg-white dark:bg-zinc-900/20">
                                            @foreach ($msg['data'] as $row)
                                                <tr>
                                                    @foreach ($row as $value)
                                                        <td class="px-2.5 py-1.5 font-bold text-zinc-950 dark:text-zinc-100">
                                                            {{ is_bool($value) ? ($value ? 'Sí' : 'No') : $value }}
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
                                <div class="mt-2.5">
                                    <details class="group bg-zinc-100/60 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-800/50 overflow-hidden">
                                        <summary class="flex justify-between items-center px-2.5 py-1.5 text-[8px] font-black uppercase tracking-wider text-zinc-500 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 select-none outline-none">
                                            <span>Ver SQL Ejecutado</span>
                                            <flux:icon name="chevron-down" class="w-3 h-3 transition-transform group-open:rotate-180" />
                                        </summary>
                                        <div class="px-2.5 pb-2.5 pt-1 border-t border-dashed border-zinc-200 dark:border-zinc-800">
                                            <pre class="font-mono text-[8px] text-emerald-400 whitespace-pre-wrap break-all bg-zinc-950 p-2 rounded-lg border border-zinc-800/80 select-all">{{ $msg['sql'] }}</pre>
                                        </div>
                                    </details>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Burbuja de Carga con Animación -->
                <div wire:loading wire:target="ask" class="flex flex-col items-start gap-1">
                    <span class="text-[8px] text-zinc-400 font-bold uppercase tracking-wider px-1">SICOE-IA</span>
                    <div class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-100 dark:border-zinc-800 rounded-2xl rounded-tl-none p-3.5 max-w-[85%] flex items-center gap-3">
                        <div class="flex gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce"></span>
                        </div>
                        <span class="text-[10px] font-bold text-zinc-500 animate-pulse italic">Consultando analítica...</span>
                    </div>
                </div>
            </div>

            <!-- Entrada del Operador (Formulario de Envío) -->
            <form wire:submit="ask" class="flex gap-2 items-center border-t border-zinc-100 dark:border-zinc-800/80 p-4 flex-shrink-0" wire:loading.attr="disabled" wire:target="ask">
                <div class="flex-1">
                    <flux:input wire:model="input" placeholder="Pregunta algo al sistema..." class="font-medium text-xs" required autocomplete="off" />
                </div>
                <button type="submit" class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-all duration-200 active:scale-95 disabled:opacity-50" wire:loading.attr="disabled" wire:target="ask">
                    <flux:icon name="paper-airplane" variant="mini" class="w-4 h-4" />
                </button>
            </form>

        @else
            <!-- ================= VISTA DE ESTADO DESACTIVADO (PÚBLICO) ================= -->
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8 space-y-5">
                <div class="w-12 h-12 rounded-2xl bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center text-zinc-400 dark:text-zinc-600 border border-zinc-150 dark:border-zinc-800 shadow-inner">
                    <flux:icon name="cpu-chip" class="w-6 h-6" />
                </div>
                <div class="space-y-2 max-w-xs">
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-tight">Copiloto IA Suspendido</h3>
                    <p class="text-[10px] text-zinc-500 leading-relaxed font-semibold">
                        El servicio de análisis conversacional de Inteligencia Artificial se encuentra desactivado temporalmente por disposición de la **Dirección de Control Escolar**.
                    </p>
                </div>
                <flux:badge size="sm" color="zinc" variant="solid" class="bg-zinc-500 text-[8px] tracking-widest font-black uppercase">Servicio Inactivo</flux:badge>
            </div>
        @endif
    </div>
</div>
