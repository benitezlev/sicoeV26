<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasJurisdiction;

class Expediente extends Model
{
    use HasJurisdiction;

   protected $fillable = [
        'user_id', 'folio', 'estatus', 'fecha_apertura', 'observaciones'
    ];

    //relaciones con otros modelos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentosExpediente::class);
    }

    // scopes para filtros comunes

    public function scopeCompletos($query)
    {
        return $query->where('estatus', 'completo');
    }

    public function scopeIncompletos($query)
    {
        return $query->where('estatus', 'incompleto');
    }

    public function scopeObservados($query)
    {
        return $query->where('estatus', 'observado');
    }

    public function scopePorCURP($query, string $curp)
    {
        return $query->whereHas('user', function ($q) use ($curp) {
            $q->where('curp', strtoupper($curp));
        });
    }

    public function scopePorSede($query, string $sede)
    {
        return $query->where('sede', $sede);
    }

    public function scopePorCiclo($query, string $ciclo)
    {
        return $query->where('ciclo', $ciclo);
    }

    public function scopePorPerfil($query, string $perfil)
    {
        return $query->whereHas('user', function ($q) use ($perfil) {
            $q->where('perfil', $perfil);
        });
    }

    public function validarDocumentosPorPerfil(): array
    {
        $perfil = $this->user->tipo; // Usamos tipo o perfil según corresponda
        $nivel = $this->user->nivel;

        // Documentos que son para este perfil Y (no tienen nivel asignado O coinciden con el nivel del usuario)
        $requeridos = DocumentoRequerido::where(function($q) use ($perfil, $nivel) {
                $q->where('perfil', $perfil)
                  ->where(function($sq) use ($nivel) {
                      $sq->whereNull('nivel')
                        ->orWhere('nivel', $nivel);
                  });
            })
            ->pluck('tipo');

        $validados = $this->documentos()->where('estatus', 'validado')->pluck('tipo');

        $faltantes = $requeridos->diff($validados);

        // Actualiza estatus institucional
        if ($faltantes->isEmpty()) {
            $this->update(['estatus' => 'completo']);
        } else {
            $tieneObservaciones = $this->documentos()->where('estatus', 'observado')->exists();
            $this->update(['estatus' => $tieneObservaciones ? 'observado' : 'incompleto']);
        }

        return $faltantes->toArray();
    }


}
