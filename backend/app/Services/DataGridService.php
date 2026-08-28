<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;

class DataGridService
{
    public function get(Request $request, string $uuid): array
    {
        $payload = $this->payload($request);
        $table = $this->table($payload['tabla'] ?? '');
        $query = $this->connection($uuid)->table($table);
        $params = $payload['sqlParams'] ?? [];

        if (($params['sqlInactive'] ?? false) !== true && ($params['sqlInactive'] ?? '') !== 'true') {
            $query->where($table.'.activo', 1);
        }
        if (!empty($payload['searchWord']) && !empty($params['fieldSearch'])) {
            $term = (string) $payload['searchWord'];
            $query->where(function ($builder) use ($params, $term): void {
                foreach ($params['fieldSearch'] as $field) {
                    $builder->orWhere($this->column($field), 'like', '%'.$term.'%');
                }
            });
        }
        foreach (($payload['filters'] ?? []) as $field => $value) {
            $query->where($this->column((string) $field), $value);
        }

        if (($payload['mode'] ?? 'rows') === 'total') {
            return [['total' => $query->count()]];
        }

        $columns = $this->columns($params['sqlCols'] ?? []);
        if ($columns !== []) {
            $query->select($columns);
        }
        $offset = max(0, (int) ($payload['offsetRecord'] ?? 0));
        $limit = $payload['showRecords'] ?? 50;
        if ($limit !== 'todos') {
            $query->offset($offset)->limit(min(500, max(1, (int) $limit)));
        }

        return $query->get()->map(fn ($row) => (array) $row)->all();
    }

    public function insert(Request $request, string $uuid): array
    {
        $payload = $this->payload($request);
        $table = $this->table($payload['tabla'] ?? '');
        $data = $this->data($payload['arrayData'] ?? []);
        unset($data['id']);
        $connection = $this->connection($uuid);
        $this->validateProjectSchedule($connection, $table, $data);
        $id = $connection->table($table)->insertGetId($data);

        return ['msg' => 'success', 'insertId' => $id];
    }

    public function update(Request $request, string $uuid): array
    {
        $payload = $this->payload($request);
        $table = $this->table($payload['tabla'] ?? '');
        $data = $this->data($payload['arrayData'] ?? []);
        $id = (int) ($data['id'] ?? 0);
        unset($data['id']);
        abort_if($id < 1, 422, 'El identificador es obligatorio.');
        $connection = $this->connection($uuid);
        $this->validateProjectSchedule($connection, $table, $data, $id);
        $connection->table($table)->where('id', $id)->update($data);

        return ['msg' => 'success'];
    }

    public function delete(Request $request, string $uuid): array
    {
        $payload = $this->payload($request);
        $table = $this->table($payload['tabla'] ?? '');
        $id = (int) ($payload['id'] ?? 0);
        abort_if($id < 1, 422, 'El identificador es obligatorio.');
        $query = $this->connection($uuid)->table($table)->where('id', $id);
        if (($payload['actionDelete'] ?? '') === 'delete') {
            $query->delete();
        } else {
            $query->update(['activo' => 0]);
        }

        return ['msg' => 'success'];
    }

    private function payload(Request $request): array
    {
        $encoded = (string) $request->input('payload', '');
        $decoded = base64_decode($encoded, true);
        $payload = is_string($decoded) ? json_decode($decoded, true) : null;
        abort_unless(is_array($payload), 422, 'Payload inválido.');
        return $payload;
    }

    private function table(string $table): string
    {
        abort_unless(in_array($table, config('datagrid.tables', []), true), 422, 'Tabla no permitida.');
        return $table;
    }

    private function columns(array $columns): array
    {
        return array_values(array_filter(array_map(fn ($column) => $this->column($column), $columns)));
    }

    private function column(string $column): string
    {
        abort_unless((bool) preg_match('/^[A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)?$/', $column), 422, 'Columna no permitida.');
        return $column;
    }

    private function data(array $data): array
    {
        foreach (array_keys($data) as $key) {
            $this->column((string) $key);
        }
        return $data;
    }

    private function validateProjectSchedule(Connection $connection, string $table, array $data, int $id = 0): void
    {
        if (!in_array($table, ['proyectos', 'proyectos_actividades', 'proyectos_subactividades'], true)) return;

        $current = $id > 0 ? (array) $connection->table($table)->where('id', $id)->first() : [];
        abort_if($id > 0 && $current === [], 404, 'El registro no existe.');
        $values = array_merge($current, $data);
        if (empty($values['fecha_inicio']) || empty($values['fecha_final'])) return;
        abort_if($values['fecha_final'] < $values['fecha_inicio'], 422, 'La fecha final debe ser posterior a la fecha inicial.');

        if ($table === 'proyectos') {
            if ($id > 0) {
                $hasOutOfRangeActivities = $connection->table('proyectos_actividades')
                    ->where('id_proyecto', $id)
                    ->where(function ($query) use ($values): void {
                        $query->where('fecha_inicio', '<', $values['fecha_inicio'])
                            ->orWhere('fecha_final', '>', $values['fecha_final']);
                    })->exists();
                abort_if($hasOutOfRangeActivities, 422, 'No se puede reducir el periodo: existen actividades fuera de las nuevas fechas.');
            }
            return;
        }

        if ($table === 'proyectos_actividades' && $id > 0) {
            $hasOutOfRangeSubactivities = $connection->table('proyectos_subactividades')
                ->where('id_actividad', $id)
                ->where(function ($query) use ($values): void {
                    $query->where('fecha_inicio', '<', $values['fecha_inicio'])
                        ->orWhere('fecha_final', '>', $values['fecha_final']);
                })->exists();
            abort_if($hasOutOfRangeSubactivities, 422, 'No se puede reducir el periodo: existen subactividades fuera de las nuevas fechas.');
        }

        $projectId = $table === 'proyectos_actividades'
            ? (int) ($values['id_proyecto'] ?? 0)
            : (int) $connection->table('proyectos_actividades')->where('id', $values['id_actividad'] ?? 0)->value('id_proyecto');
        $project = $connection->table('proyectos')->where('id', $projectId)->first(['fecha_inicio', 'fecha_final']);
        abort_if(!$project, 422, 'El proyecto asociado no existe.');
        abort_if($values['fecha_inicio'] < $project->fecha_inicio || $values['fecha_final'] > $project->fecha_final, 422, 'Las fechas deben estar dentro del periodo del proyecto.');
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
