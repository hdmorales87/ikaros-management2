<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class RoleService
{
    public function managementData(string $uuid): array
    {
        $connection = $this->connection($uuid);

        return [
            'roles' => $connection->table('roles')->select(['id', 'nombre'])->where('activo', 1)->orderBy('nombre')->get()->map(fn ($role) => (array) $role)->all(),
            'permissions' => $connection->table('permisos')->select(['id', 'nombre', 'descripcion'])->where('activo', 1)->orderBy('nombre')->get()->map(fn ($permission) => (array) $permission)->all(),
        ];
    }

    public function permissions(int $roleId, string $uuid): array
    {
        return $this->connection($uuid)->table('roles_permisos')->where('id_rol', $roleId)->pluck('id_permiso')->map(fn ($id) => (int) $id)->all();
    }

    public function savePermissions(int $roleId, array $permissions, string $uuid): void
    {
        $connection = $this->connection($uuid);
        $connection->transaction(function () use ($connection, $roleId, $permissions): void {
            $connection->table('roles_permisos')->where('id_rol', $roleId)->delete();
            if ($permissions !== []) {
                $connection->table('roles_permisos')->insert(array_map(
                    fn (int $permission) => ['id_rol' => $roleId, 'id_permiso' => $permission],
                    $permissions,
                ));
            }
        });
    }

    private function connection(string $uuid): Connection
    {
        if ($uuid === '') {
            throw new \InvalidArgumentException('El identificador de empresa es obligatorio.');
        }
        return (new Company())->getConnectionByUUID($uuid);
    }
}
