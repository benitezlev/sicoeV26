<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasJurisdiction
{
    /**
     * Boot the trait to apply global scopes.
     */
    public static function bootHasJurisdiction()
    {
        if (app()->runningInConsole()) {
            return;
        }

        static::addGlobalScope('jurisdiction', function (Builder $builder) {
            $user = Auth::user();

            if (!$user || $user->hasRole('admin_ti')) {
                return;
            }

            $model = new static;
            $table = $model->getTable();

            // Lógica para filtrar Usuarios
            if ($table === 'users') {
                if ($user->plantel_id) {
                    $builder->where('plantel_id', $user->plantel_id);
                } elseif ($user->municipio_id) {
                    $builder->where('municipio_id', $user->municipio_id);
                } elseif ($user->nivel) {
                    $builder->where('nivel', $user->nivel);
                }
            }
            
            // Lógica para filtrar Expedientes (vía relación user)
            if ($table === 'expedientes') {
                $builder->whereHas('user', function ($q) use ($user) {
                    if ($user->plantel_id) {
                        $q->where('plantel_id', $user->plantel_id);
                    } elseif ($user->municipio_id) {
                        $q->where('municipio_id', $user->municipio_id);
                    } elseif ($user->nivel) {
                        $q->where('nivel', $user->nivel);
                    }
                });
            }

            // Lógica para filtrar Grupos
            if ($table === 'grupos') {
                if ($user->plantel_id) {
                    $builder->where('plantel_id', $user->plantel_id);
                }
                // Si el grupo necesitara nivel, deberíamos añadirlo a la tabla grupos
            }
        });
    }

    /**
     * Scope para ignorar las restricciones (útil para auditoría)
     */
    public function scopeAllJurisdictions($query)
    {
        return $query->withoutGlobalScope('jurisdiction');
    }
}
