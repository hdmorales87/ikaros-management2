<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_seguimientos';

    protected $fillable = [
        'id_maestro',
        'estado',
        'observacion',
        'id_usuario',
        'fecha',
        'causa',
    ];

    protected $casts = [
        'id_maestro' => 'integer',
        'id_usuario' => 'integer',
        'fecha' => 'datetime',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'id_maestro');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
