<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asistencia;
use Carbon\Carbon;

class RevisarAsistencias extends Command
{
    protected $signature = 'asistencias:revisar';
    protected $description = 'Revisa asistencias y actualiza estados según tiempos';

    public function handle()
    {
        $now = Carbon::now();

        // Marcar como no_subido si pasaron 4 horas sin archivo
        Asistencia::whereNull('archivo')
            ->where('created_at','<',$now->subHours(4))
            ->update(['estado'=>'no_subido']);

        // Marcar como expirado si pasaron 3 horas sin validación
        Asistencia::where('estado','pendiente')
            ->where('subido_at','<',$now->subHours(3))
            ->update(['estado'=>'expirado']);

        $this->info('Revisión de asistencias completada.');
    }
}
