<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class FichaTecnicaService
{
    private const TABLES = ['activos', 'proyectos'];

    public function fields(string $table, int $masterId, string $uuid): array
    {
        $this->assertTable($table);
        $connection = $this->connection($uuid);
        $query = $connection->table($table.'_tipos_campos_ficha as fields')
            ->leftJoin($table.'_ficha as values', function ($join) use ($masterId): void {
                $join->on('values.id_campo', '=', 'fields.id')->where('values.id_maestro', $masterId);
            })
            ->select(['fields.id', 'fields.nombre', 'fields.tipo', 'fields.validacion', 'fields.longitud', 'values.valor'])
            ->where('fields.activo', 1);
        if ($table === 'activos') {
            $typeId = $connection->table('activos')->where('id', $masterId)->value('id_tipo');
            $query->where('fields.id_tipo', $typeId);
        }
        return $query->orderBy('fields.id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function values(string $table, int $fieldId, string $uuid): array
    {
        $this->assertTable($table);
        return $this->connection($uuid)->table($table.'_tipos_campos_ficha_valores')
            ->where('id_campo', $fieldId)->where('activo', 1)->orderBy('id')
            ->get(['id', 'valor'])->map(fn ($row) => (array) $row)->all();
    }

    public function save(string $table, int $masterId, array $fields, string $uuid): void
    {
        $this->assertTable($table);
        $connection = $this->connection($uuid);
        $rows = [];
        foreach ($fields as $key => $value) {
            abort_unless(preg_match('/^campo_[0-9]+$/', (string) $key) === 1, 422, 'Campo de ficha inválido.');
            $rows[] = ['id_maestro' => $masterId, 'id_campo' => (int) str_replace('campo_', '', $key), 'valor' => $value];
        }
        $connection->transaction(function () use ($connection, $table, $masterId, $rows): void {
            $connection->table($table.'_ficha')->where('id_maestro', $masterId)->delete();
            if ($rows !== []) $connection->table($table.'_ficha')->insert($rows);
        });
    }

    private function assertTable(string $table): void
    {
        abort_unless(in_array($table, self::TABLES, true), 422, 'Tabla de ficha no permitida.');
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
