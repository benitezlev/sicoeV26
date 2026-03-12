<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use HasProfilePhoto;


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'paterno',
        'materno',
        'nombre',
        'email',
        'password',
        'username',
        'name',
        'email',
        'password',
        'curp',
        'cuip',
        'cup',
        'dependencia',
        'adscripcion',
        'perfil',
        'sexo',
        'tipo',

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




}
