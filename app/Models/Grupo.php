<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
     protected $fillable = [
        'nombre','plantel_id','curso_id','periodo','estado',
        'fecha_inicio','fecha_fin','hora_inicio','hora_fin','total_horas'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];



    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function plantel()
    {
        return $this->belongsTo(Plantel::class);
    }

    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'grupo_user')
            ->where('tipo', 'alumno')
            ->withPivot('fecha_asignacion', 'estado')
            ->withTimestamps();
    }


    public function expediente()
    {
        return $this->hasMany(GrupoExpediente::class);
    }

    public function docente()
    {
        if (!$this->docente_id) {
            return null;
        }

        $response = Http::withToken(config('services.sad.token'))
            ->get(config('services.sad.url').'/docentes/'.$this->docente_id);

        return $response->successful() ? $response->json() : null;
    }

    public function diasHabilesEntreFechas(): array
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) return [];

        $inicio = Carbon::parse($this->fecha_inicio)->startOfDay();
        $fin    = Carbon::parse($this->fecha_fin)->endOfDay();

        if ($inicio->gt($fin)) return [];

        $map = [1=>'LU', 2=>'MA', 3=>'MI', 4=>'JU', 5=>'VI'];
        $dias = [];

        for ($f = $inicio->copy(); $f->lte($fin); $f->addDay()) {
            if ($f->isWeekend()) continue;
            $dias[] = [
                'fecha'       => $f->copy(),
                'abreviado'   => $map[$f->dayOfWeekIso],
                'hora_inicio' => $this->hora_inicio ?? '09:00',
                'hora_fin'    => $this->hora_fin ?? '18:00',
            ];
        }

        return $dias;
    }




}
