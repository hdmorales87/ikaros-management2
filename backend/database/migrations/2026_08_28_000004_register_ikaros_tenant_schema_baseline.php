<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.default');
        $tables = [
            'activos', 'activos_adjuntos', 'activos_compras', 'activos_compras_adjuntos', 'activos_estados',
            'activos_ficha', 'activos_load_excel', 'activos_novedades', 'activos_tipos',
            'activos_tipos_campos_ficha', 'activos_tipos_campos_ficha_valores', 'areas_servicio',
            'areas_servicio_categorias', 'areas_servicio_subcategorias', 'areas_sla', 'capacitaciones',
            'capacitaciones_adjuntos', 'capacitaciones_usuarios', 'capacitaciones_usuarios_adjuntos',
            'configuracion_modulos', 'configuracion_modulos_extensiones', 'configuracion_tipos_documentacion',
            'conocimiento', 'conocimiento_adjuntos', 'conocimiento_reputacion', 'departamentos',
            'departamentos_ubicaciones', 'dias_festivos', 'document_types', 'encuesta_satisfaccion_preguntas',
            'encuesta_satisfaccion_respuestas', 'encuesta_terceros', 'encuesta_terceros_preguntas',
            'encuesta_terceros_respuestas', 'imap', 'imap_reglas', 'incidencias', 'incidencias_adjuntos',
            'incidencias_seguimientos', 'iniciativas', 'iniciativas_adjuntos', 'iniciativas_comites',
            'iniciativas_comites_aprobadores', 'iniciativas_comites_aprobadores_seguimientos',
            'iniciativas_estados', 'iniciativas_propietarios', 'log', 'monedas', 'permisos',
            'politicas_seguridad', 'proyectos', 'proyectos_actividades', 'proyectos_actividades_adjuntos',
            'proyectos_actividades_estados', 'proyectos_actividades_responsables', 'proyectos_aprobadores',
            'proyectos_colaboradores', 'proyectos_documentos', 'proyectos_documentos_adjuntos',
            'proyectos_estados', 'proyectos_ficha', 'proyectos_gantt_historico', 'proyectos_horas_registro',
            'proyectos_horas_registro_adjuntos', 'proyectos_horas_registro_seguimientos',
            'proyectos_kanban_seguimientos', 'proyectos_niveles_riesgos', 'proyectos_riesgos',
            'proyectos_riesgos_impacto', 'proyectos_riesgos_probabilidad', 'proyectos_seguimientos',
            'proyectos_subactividades', 'proyectos_subactividades_adjuntos', 'proyectos_tipos_campos_ficha',
            'proyectos_tipos_campos_ficha_valores', 'reputacion_estrellas', 'roles', 'roles_permisos',
            'servicios', 'servicios_adjuntos', 'servicios_seguimientos', 'smtp', 'solicitudes_activos',
            'solicitudes_estado', 'solicitudes_impacto', 'solicitudes_notificaciones', 'solicitudes_prioridades',
            'solicitudes_urgencia', 'terceros', 'terceros_adjuntos', 'terceros_asignados', 'terceros_contratos',
            'terceros_contratos_adjuntos', 'terceros_contratos_estados', 'terceros_contratos_notificaciones',
            'terceros_contratos_pagos', 'terceros_contratos_pagos_adjuntos', 'terceros_contratos_planes_pagos',
            'users', 'users_adjuntos', 'users_load_excel',
        ];

        foreach ($tables as $table) {
            if (!Schema::connection($connection)->hasTable($table)) {
                throw new RuntimeException("La tabla tenant requerida [{$table}] no existe en la conexión [{$connection}].");
            }
        }
    }

    public function down(): void
    {
        // La línea base representa un esquema existente y no debe eliminar tablas.
    }
};