<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class InitiativeCommitteeService
{
    public function list(string $uuid): array
    {
        return $this->connection($uuid)->table('iniciativas_comites')->select(['id', 'id_iniciativa', 'nombre', 'descripcion', 'orden', 'estado_validacion'])->where('activo', 1)->orderBy('id_iniciativa')->orderBy('orden')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function create(string $uuid, array $data): array
    {
        $connection = $this->connection($uuid);
        abort_unless($connection->table('iniciativas')->where('id', $data['id_iniciativa'])->where('activo', 1)->exists(), 422, 'La iniciativa no existe o está inactiva.');
        $id = $connection->table('iniciativas_comites')->insertGetId([...$data, 'estado_validacion' => 1, 'activo' => 1]);
        return (array) $connection->table('iniciativas_comites')->select(['id', 'id_iniciativa', 'nombre', 'descripcion', 'orden', 'estado_validacion'])->where('id', $id)->first();
    }

    public function approvers(string $uuid, int $committeeId): ?array
    {
        $connection = $this->connection($uuid);
        if (!$connection->table('iniciativas_comites')->where('id', $committeeId)->where('activo', 1)->exists()) return null;
        return $connection->table('iniciativas_comites_aprobadores')->select(['id', 'id_user', 'estado_validacion'])->where('id_comite', $committeeId)->where('activo', 1)->get()->map(fn ($row) => (array) $row)->all();
    }

    public function addApprover(string $uuid, int $committeeId, int $userId): ?array
    {
        $connection = $this->connection($uuid);
        $committee = $connection->table('iniciativas_comites')->where('id', $committeeId)->where('activo', 1)->first(['id_iniciativa']);
        if (!$committee) return null;
        abort_unless($connection->table('users')->where('id', $userId)->where('activo', 1)->exists(), 422, 'El usuario no existe o está inactivo.');
        abort_if($connection->table('iniciativas_comites_aprobadores')->where('id_comite', $committeeId)->where('id_user', $userId)->where('activo', 1)->exists(), 422, 'El usuario ya es aprobador.');
        $id = $connection->table('iniciativas_comites_aprobadores')->insertGetId(['id_comite' => $committeeId, 'id_iniciativa' => $committee->id_iniciativa, 'id_user' => $userId, 'estado_validacion' => 1, 'activo' => 1]);
        return (array) $connection->table('iniciativas_comites_aprobadores')->select(['id', 'id_user', 'estado_validacion'])->where('id', $id)->first();
    }

    public function removeApprover(string $uuid, int $committeeId, int $approverId): bool
    {
        return $this->connection($uuid)->table('iniciativas_comites_aprobadores')->where('id', $approverId)->where('id_comite', $committeeId)->where('activo', 1)->update(['activo' => 0]) > 0;
    }

    private function connection(string $uuid): Connection { abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.'); return (new Company())->getConnectionByUUID($uuid); }
}
