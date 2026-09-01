<?php

namespace App\Services;

use App\Models\Company;

class LocationService
{
    public function list(string $uuid): array
    {
        return $this->connection($uuid)->table('departamentos_ubicaciones')
            ->select(['id', 'id_departamento', 'nombre'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($location) => (array) $location)
            ->all();
    }

    public function create(string $uuid, array $data): array
    {
        $connection = $this->connection($uuid);
        $this->ensureActiveDepartment($connection, (int) $data['id_departamento']);
        $locationId = $connection->table('departamentos_ubicaciones')->insertGetId([
            'id_departamento' => $data['id_departamento'],
            'nombre' => $data['nombre'],
            'activo' => 1,
        ]);

        return $this->find($connection, $locationId);
    }

    public function update(string $uuid, int $locationId, array $data): ?array
    {
        $connection = $this->connection($uuid);
        $this->ensureActiveDepartment($connection, (int) $data['id_departamento']);
        $updated = $connection->table('departamentos_ubicaciones')
            ->where('id', $locationId)
            ->where('activo', 1)
            ->update(['id_departamento' => $data['id_departamento'], 'nombre' => $data['nombre']]);
        return $updated ? $this->find($connection, $locationId) : null;
    }

    public function deactivate(string $uuid, int $locationId): bool
    {
        return $this->connection($uuid)->table('departamentos_ubicaciones')
            ->where('id', $locationId)
            ->where('activo', 1)
            ->update(['activo' => 0]) > 0;
    }

    private function find($connection, int $locationId): array
    {
        return (array) $connection->table('departamentos_ubicaciones')
            ->select(['id', 'id_departamento', 'nombre'])
            ->where('id', $locationId)
            ->first();
    }

    private function ensureActiveDepartment($connection, int $departmentId): void
    {
        abort_unless($connection->table('departamentos')->where('id', $departmentId)->where('activo', 1)->exists(), 422, 'El departamento no existe o está inactivo.');
    }

    private function connection(string $uuid)
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
