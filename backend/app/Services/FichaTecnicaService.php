<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class FichaTecnicaService
{
    private const TABLES = ['activos', 'proyectos'];

    public function save(string $table, int $masterId, array $fields, string $uuid): void
    {
        abort_unless(in_array($table, self::TABLES, true), 422, 'Tabla de ficha no permitida.');
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

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
