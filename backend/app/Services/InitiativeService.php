<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class InitiativeService
{
    private const FIELDS = ['codigo', 'nomenclatura', 'nombre', 'id_departamento', 'id_propietario', 'fecha_inicio', 'hora_inicio', 'fecha_final', 'hora_final', 'presupuesto', 'tiempo', 'descripcion', 'beneficios_cualitativos', 'beneficios_cuantitativos', 'escenario_pesimista', 'escenario_optimista', 'estado'];

    public function list(string $uuid): array
    {
        return $this->connection($uuid)->table('iniciativas')->select(['id', ...self::FIELDS])->where('activo', 1)->orderByDesc('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function create(string $uuid, array $data): array
    {
        $connection = $this->connection($uuid);
        $this->validateRelations($connection, $data);
        $this->validateDates($data);
        $id = $connection->table('iniciativas')->insertGetId([...$data, 'estado' => 1, 'fecha_registro' => now(), 'activo' => 1]);
        return $this->find($connection, $id);
    }

    public function update(string $uuid, int $initiativeId, array $data): ?array
    {
        $connection = $this->connection($uuid);
        $current = $this->find($connection, $initiativeId);
        if (!$current) return null;
        $values = [...$current, ...$data];
        $this->validateRelations($connection, $values);
        $this->validateDates($values);
        $connection->table('iniciativas')->where('id', $initiativeId)->update($data);
        return $this->find($connection, $initiativeId);
    }

    private function find(Connection $connection, int $initiativeId): ?array
    {
        $initiative = $connection->table('iniciativas')->select(['id', ...self::FIELDS])->where('id', $initiativeId)->where('activo', 1)->first();
        return $initiative ? (array) $initiative : null;
    }

    private function validateRelations(Connection $connection, array $data): void
    {
        abort_unless($connection->table('departamentos')->where('id', $data['id_departamento'])->where('activo', 1)->exists(), 422, 'El departamento no existe o está inactivo.');
        abort_unless($connection->table('users')->where('id', $data['id_propietario'])->where('activo', 1)->exists(), 422, 'El propietario no existe o está inactivo.');
    }

    private function validateDates(array $data): void
    {
        abort_if($data['fecha_final'] < $data['fecha_inicio'] || ($data['fecha_final'] === $data['fecha_inicio'] && $data['hora_final'] < $data['hora_inicio']), 422, 'La fecha y hora final deben ser posteriores a las iniciales.');
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
