<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasJurisdiction;

class Grupo extends Model
{
    use HasJurisdiction;
     protected $fillable = [
        'nombre','plantel_id','curso_id','docente_id','periodo','estado',
        'fecha_inicio','fecha_fin','hora_inicio','hora_fin','total_horas',
        'dias_clase', 'formato_especial', 'tipo_grupo'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'dias_clase' => 'array',
        'formato_especial' => 'boolean',
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
            ->withPivot('fecha_asignacion', 'estado', 'fecha_baja', 'motivo_baja', 'baja_registrada_por')
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

        $map = [1=>'LU', 2=>'MA', 3=>'MI', 4=>'JU', 5=>'VI', 6=>'SA', 7=>'DO'];
        $diasConfigurados = $this->dias_clase ?? [1, 2, 3, 4, 5];
        
        // Asegurar que sea un arreglo (caso de doble codificación o string)
        if (is_string($diasConfigurados)) {
            $diasConfigurados = json_decode($diasConfigurados, true) ?: explode(',', $diasConfigurados);
        }
        
        $dias = [];

        for ($f = $inicio->copy(); $f->lte($fin); $f->addDay()) {
            if (!in_array($f->dayOfWeekIso, $diasConfigurados)) continue;
            
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
