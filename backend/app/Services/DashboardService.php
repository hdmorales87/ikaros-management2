<?php

namespace App\Services;

use App\Models\Company;

class DashboardService
{
    public function summary(string $uuid): array
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        $connection = (new Company())->getConnectionByUUID($uuid);

        return [
            'incidents' => $connection->table('incidencias')->where('activo', 1)->count(),
            'services' => $connection->table('servicios')->where('activo', 1)->count(),
            'assets' => $connection->table('activos')->where('activo', 1)->count(),
            'projects' => $connection->table('proyectos')->where('activo', 1)->count(),
            'trainings' => $connection->table('capacitaciones')->where('activo', 1)->count(),
        ];
    }
}
