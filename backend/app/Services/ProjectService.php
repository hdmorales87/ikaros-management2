<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class ProjectService
{
    public function paginate(string $uuid, string $search, int $perPage, string $sort): array
    {
        $query = $this->connection($uuid)->table('proyectos')
            ->select(['id', 'codigo', 'nombre', 'estado', 'fecha_inicio', 'fecha_final'])
            ->where('activo', 1);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%');
            });
        }

        $this->applySort($query, $sort);
        $paginator = $query->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function update(int $projectId, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $current = $connection->table('proyectos')->where('id', $projectId)->where('activo', 1)->first();
        if (!$current) {
            return null;
        }

        $values = array_merge((array) $current, $data);
        abort_if($values['fecha_final'] < $values['fecha_inicio'], 422, 'La fecha final debe ser posterior a la inicial.');
        $hasOutOfRangeActivities = $connection->table('proyectos_actividades')
            ->where('id_proyecto', $projectId)
            ->where(function ($query) use ($values): void {
                $query->where('fecha_inicio', '<', $values['fecha_inicio'])
                    ->orWhere('fecha_final', '>', $values['fecha_final']);
            })
            ->exists();
        abort_if($hasOutOfRangeActivities, 422, 'No se puede reducir el periodo: existen actividades fuera de las nuevas fechas.');

        $connection->table('proyectos')->where('id', $projectId)->update($data);
        return (array) $connection->table('proyectos')
            ->select(['id', 'codigo', 'nombre', 'estado', 'fecha_inicio', 'fecha_final'])
            ->where('id', $projectId)
            ->first();
    }

    public function createActivity(int $projectId, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $project = $connection->table('proyectos')
            ->where('id', $projectId)
            ->where('activo', 1)
            ->first(['fecha_inicio', 'fecha_final']);
        if (!$project) {
            return null;
        }

        abort_if($data['fecha_final'] < $data['fecha_inicio'], 422, 'La fecha final debe ser posterior a la inicial.');
        abort_if($data['fecha_inicio'] < $project->fecha_inicio || $data['fecha_final'] > $project->fecha_final, 422, 'La actividad debe estar dentro del periodo del proyecto.');
        abort_if($data['fecha_inicio'] === $data['fecha_final'] && $data['hora_final'] < $data['hora_inicio'], 422, 'La hora final debe ser posterior a la hora inicial.');

        $activityId = $connection->table('proyectos_actividades')->insertGetId([
            'id_proyecto' => $projectId,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'fecha_inicio' => $data['fecha_inicio'],
            'hora_inicio' => $data['hora_inicio'],
            'fecha_final' => $data['fecha_final'],
            'hora_final' => $data['hora_final'],
            'estado' => 0,
        ]);

        return (array) $connection->table('proyectos_actividades')->where('id', $activityId)->first();
    }

    public function createRisk(int $projectId, string $name, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $exists = $connection->table('proyectos')->where('id', $projectId)->where('activo', 1)->exists();
        if (!$exists) {
            return null;
        }

        $riskId = $connection->table('proyectos_riesgos')->insertGetId([
            'id_proyecto' => $projectId,
            'nombre' => $name,
            'estado' => 0,
        ]);

        return (array) $connection->table('proyectos_riesgos')->where('id', $riskId)->first();
    }
    public function activities(int $projectId, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        if (!$connection->table('proyectos')->where('id', $projectId)->where('activo', 1)->exists()) {
            return null;
        }

        return $connection->table('proyectos_actividades')
            ->select(['id', 'nombre', 'fecha_inicio', 'fecha_final', 'estado'])
            ->where('id_proyecto', $projectId)
            ->where('activo', 1)
            ->orderBy('fecha_inicio')
            ->get()
            ->map(fn ($activity) => (array) $activity)
            ->all();
    }

    public function allActivities(string $uuid): array
    {
        return $this->connection($uuid)->table('proyectos_actividades')
            ->select(['id', 'id_proyecto', 'nombre', 'descripcion', 'fecha_inicio', 'hora_inicio', 'fecha_final', 'hora_final', 'id_responsable', 'estado'])
            ->where('activo', 1)
            ->orderBy('fecha_inicio')
            ->get()
            ->map(fn ($activity) => (array) $activity)
            ->all();
    }

    public function subactivities(int $activityId, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        if (!$connection->table('proyectos_actividades')->where('id', $activityId)->where('activo', 1)->exists()) {
            return null;
        }

        return $connection->table('proyectos_subactividades')
            ->select(['id', 'nombre', 'descripcion', 'fecha_inicio', 'hora_inicio', 'fecha_final', 'hora_final', 'estado'])
            ->where('id_actividad', $activityId)
            ->where('activo', 1)
            ->orderBy('fecha_inicio')
            ->get()
            ->map(fn ($subactivity) => (array) $subactivity)
            ->all();
    }

    public function risks(int $projectId, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        if (!$connection->table('proyectos')->where('id', $projectId)->where('activo', 1)->exists()) {
            return null;
        }

        return $connection->table('proyectos_riesgos')
            ->select(['id', 'nombre', 'descripcion', 'mitigacion', 'estado'])
            ->where('id_proyecto', $projectId)
            ->where('activo', 1)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($risk) => (array) $risk)
            ->all();
    }

    private function applySort($query, string $sort): void
    {
        $allowed = ['id', 'codigo', 'nombre', 'estado', 'fecha_inicio', 'fecha_final'];
        $applied = false;

        foreach (array_filter(explode(',', $sort)) as $requested) {
            $column = ltrim($requested, '-');
            abort_unless(in_array($column, $allowed, true), 422, 'Campo de ordenamiento no permitido.');
            $query->orderBy($column, str_starts_with($requested, '-') ? 'desc' : 'asc');
            $applied = true;
        }

        if (!$applied) {
            $query->orderByDesc('id');
        }
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}