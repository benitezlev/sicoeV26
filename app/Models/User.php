<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasJurisdiction;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasRoles;
    use HasJurisdiction;
    use HasProfilePhoto;
    use InteractsWithMedia;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'paterno',
        'materno',
        'email',
        'password',
        'username',
        'curp',
        'cuip',
        'cup',
        'sexo',
        'tipo',
        'nivel',
        'perfil_data',
        'plantel_id',
        'municipio_id',
        'firma_digital',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'perfil_data' => 'array',
        ];
    }


    public function calificacion($unidad)
    {
        return $this->calificaciones()
            ->where('unidad', $unidad)
            ->where('grupo_id', session('grupo_id'))
            ->value('calificacion') ?? '-';
    }

    public function promedio()
    {
        $calificaciones = $this->calificaciones()
            ->where('grupo_id', session('grupo_id'))
            ->pluck('calificacion');

        return $calificaciones->count()
            ? number_format($calificaciones->avg(), 1)
            : '-';
    }

    public function asistencia($fecha)
    {
        return $this->asistencias()
            ->where('grupo_id', session('grupo_id'))
            ->whereDate('fecha', $fecha)
            ->value('estatus') ?? '-';
    }

    public function porcentajeAsistencia($fechas)
    {
        $total = count($fechas);
        $presentes = $this->asistencias()
            ->where('grupo_id', session('grupo_id'))
            ->whereIn('fecha', $fechas)
            ->where('estatus', 'presente')
            ->count();

        return $total ? round(($presentes / $total) * 100) : 0;
    }

    public function cardexDocente()
    {
        return $this->hasOne(CardexDocente::class, 'docente_id');
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->paterno} {$this->materno}");
    }

    public function scopePorPerfil($query, $perfil)
    {
        if (!empty($perfil)) {
            $query->where('perfil', $perfil);
        }
    }

    public function scopeEsAlumno($query)
    {
        $query->whereHas('roles', fn($q) => $q->where('name', 'alumno'));
    }

    public function scopeDelMunicipio($query, $municipioId)
    {
        if ($municipioId) {
            return $query->where('municipio_id', $municipioId);
        }
    }

    public function scopeDelNivel($query, $nivel)
    {
        if ($nivel) {
            return $query->where('nivel', $nivel);
        }
    }




    public function movimientos()
    {
        return $this->hasMany(UsuarioMovimiento::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_user')->withPivot('estado', 'fecha_asignacion');
    }
}
