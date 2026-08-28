<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'id_usuario',
        'asunto',
        'descripcion',
        'id_area',
        'id_categoria',
        'id_subcategoria',
        'prioridad',
        'impacto',
        'urgencia',
        'estado',
        'tipo',
        'fecha_creacion',
        'fecha_asignacion_incidencia',
        'fecha_asignacion_problema',
        'fecha_asignacion_servicio',
        'fecha_solucion_incidencia',
        'fecha_solucion_problema',
        'fecha_solucion_servicio',
        'fecha_vencimiento_incidencia',
        'fecha_vencimiento_problema',
        'fecha_vencimiento_servicio',
        'fecha_finalizacion',
        'id_tecnico_incidencia',
        'id_tecnico_problema',
        'id_tecnico_servicio',
        'problema',
        'fecha_problema',
        'incidencia_cumplimiento',
        'problema_cumplimiento',
        'servicio_cumplimiento',
        'tipo_finalizacion',
        'puntaje_encuesta',
        'gestion_cambio',
        'causa',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_asignacion_incidencia' => 'datetime',
        'fecha_asignacion_problema' => 'datetime',
        'fecha_asignacion_servicio' => 'datetime',
        'fecha_solucion_incidencia' => 'datetime',
        'fecha_solucion_problema' => 'datetime',
        'fecha_solucion_servicio' => 'datetime',
        'fecha_vencimiento_incidencia' => 'datetime',
        'fecha_vencimiento_problema' => 'datetime',
        'fecha_vencimiento_servicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'fecha_problema' => 'datetime',
        'prioridad' => 'integer',
        'impacto' => 'integer',
        'urgencia' => 'integer',
        'estado' => 'integer',
        'id_usuario' => 'integer',
        'id_area' => 'integer',
        'id_categoria' => 'integer',
        'id_subcategoria' => 'integer',
        'id_tecnico_incidencia' => 'integer',
        'id_tecnico_problema' => 'integer',
        'id_tecnico_servicio' => 'integer',
        'problema' => 'boolean',
        'incidencia_cumplimiento' => 'boolean',
        'problema_cumplimiento' => 'boolean',
        'servicio_cumplimiento' => 'boolean',
        'puntaje_encuesta' => 'float',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function tecnicoIncidencia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tecnico_incidencia');
    }

    public function tecnicoProblema(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tecnico_problema');
    }

    public function tecnicoServicio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tecnico_servicio');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaServicio::class, 'id_area');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SolicitudSeguimiento::class, 'id_maestro');
    }
}
