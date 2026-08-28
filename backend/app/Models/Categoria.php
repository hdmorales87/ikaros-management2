<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'id_area',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'id_area' => 'integer',
        'activo' => 'boolean',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaServicio::class, 'id_area');
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_categoria');
    }
}
