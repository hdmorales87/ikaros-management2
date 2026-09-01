<?php

namespace App\Services;

use App\Models\Company;

class OperationalReportService
{
    public function requests(string $uuid): array
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        $connection = (new Company())->getConnectionByUUID($uuid);
        $incidents = $connection->table('incidencias')->select(['id', 'asunto', 'estado', 'prioridad', 'fecha', 'problema'])->where('activo', 1)->get()
            ->map(fn ($row) => ['id' => $row->id, 'asunto' => $row->asunto, 'estado' => $row->estado, 'prioridad' => $row->prioridad, 'fecha' => $row->fecha, 'tipo' => filter_var($row->problema, FILTER_VALIDATE_BOOLEAN) ? 'Problema' : 'Incidencia']);
        $services = $connection->table('servicios')->select(['id', 'asunto', 'estado', 'prioridad', 'fecha'])->where('activo', 1)->get()
            ->map(fn ($row) => ['id' => $row->id, 'asunto' => $row->asunto, 'estado' => $row->estado, 'prioridad' => $row->prioridad, 'fecha' => $row->fecha, 'tipo' => 'Servicio']);

        return $incidents->merge($services)->sortByDesc('fecha')->values()->all();
    }
}
