<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AreaServicio extends Model
{
    use HasFactory;

    protected $table = 'areas_servicio';

    protected $fillable = [
        'nombre',
        'capacidad_atencion',
        'activo',
    ];

    protected $casts = [
        'capacidad_atencion' => 'integer',
        'activo' => 'boolean',
    ];

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_area');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'id_area');
    }
}
