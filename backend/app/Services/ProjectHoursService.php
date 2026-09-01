<?php

namespace App\Services;

use App\Models\Company;

class ProjectHoursService
{
    public function list(string $uuid): array
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid)->table('proyectos_horas_registro')
            ->select([
                'id', 'id_colaborador', 'id_proyecto', 'horas_ejecutadas', 'horas_planeadas',
                'observaciones_ejecutadas', 'observaciones_planeadas', 'estado_validacion_ejecutadas',
                'estado_validacion_planeadas',
            ])
            ->where('activo', 1)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }
}
