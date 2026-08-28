<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nombre',
        'segundo_nombre',
        'apellido',
        'segundo_apellido',
        'email',
        'password',
        'id_tipo_documento',
        'documento',
        'id_rol',
        'id_departamento',
        'id_area',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'sexo',
        'idioma',
        'imagen_usuario',
        'activo',
        'acceso_sistema',
        'token',
        'fecha_cambio_password',
        'intentos_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_cambio_password' => 'datetime',
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'acceso_sistema' => 'boolean',
        'sexo' => 'integer',
        'idioma' => 'integer',
        'id_rol' => 'integer',
        'id_departamento' => 'integer',
        'id_area' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_usuario');
    }
}
