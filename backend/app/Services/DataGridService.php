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
        $id = $this->connection($uuid)->table($table)->insertGetId($data);

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
        $this->connection($uuid)->table($table)->where('id', $id)->update($data);

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

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
