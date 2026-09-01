<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class ContractService
{
    public function paginate(int $thirdPartyId, string $type, string $uuid, string $search, int $perPage, string $sort): ?array
    {
        $connection = $this->connection($uuid);
        $thirdPartyExists = $connection->table('terceros')
            ->where('id', $thirdPartyId)
            ->where('activo', 1)
            ->where($type, 'true')
            ->exists();
        if (!$thirdPartyExists) {
            return null;
        }

        $query = $connection->table('terceros_contratos')
            ->select([
                'id', 'id_tercero', 'nombre', 'tipo_contrato', 'estado', 'objeto_contrato',
                'id_moneda', 'monto', 'iva', 'id_responsable_ejecucion', 'fecha_inicio',
                'fecha_vencimiento', 'id_plan_pago', 'numero_pagos', 'id_responsable_pago',
                'nombre_responsable_pago', 'email_responsable_pago', 'renovacion_automatica',
                'observaciones', 'tipo',
            ])
            ->where('id_tercero', $thirdPartyId)
            ->where('tipo', $type)
            ->where('activo', 1);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nombre', 'like', '%'.$search.'%')
                    ->orWhere('tipo_contrato', 'like', '%'.$search.'%')
                    ->orWhere('objeto_contrato', 'like', '%'.$search.'%');
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

    public function payments(int $thirdPartyId, int $contractId, string $type, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $contractExists = $connection->table('terceros_contratos')
            ->where('id', $contractId)
            ->where('id_tercero', $thirdPartyId)
            ->where('tipo', $type)
            ->where('activo', 1)
            ->exists();
        if (!$contractExists) {
            return null;
        }

        return $connection->table('terceros_contratos_pagos')
            ->select(['id', 'numero_factura', 'fecha_factura', 'valor', 'id_usuario'])
            ->where('id_contrato', $contractId)
            ->where('activo', 1)
            ->orderByDesc('fecha_factura')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($payment) => (array) $payment)
            ->all();
    }

    public function create(int $thirdPartyId, string $type, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        if (!$this->thirdPartyExists($connection, $thirdPartyId, $type)) {
            return null;
        }

        abort_if(($data['fecha_vencimiento'] ?? null) !== null && ($data['fecha_inicio'] ?? null) !== null && $data['fecha_vencimiento'] < $data['fecha_inicio'], 422, 'La fecha de vencimiento debe ser posterior a la de inicio.');
        $contractId = $connection->table('terceros_contratos')->insertGetId([
            ...$data,
            'id_tercero' => $thirdPartyId,
            'tipo' => $type,
            'activo' => 1,
        ]);

        return $this->contract($connection, $thirdPartyId, $contractId, $type);
    }

    public function update(int $thirdPartyId, int $contractId, string $type, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $current = $this->contract($connection, $thirdPartyId, $contractId, $type);
        if (!$current) {
            return null;
        }

        $values = array_merge($current, $data);
        abort_if(($values['fecha_vencimiento'] ?? null) !== null && ($values['fecha_inicio'] ?? null) !== null && $values['fecha_vencimiento'] < $values['fecha_inicio'], 422, 'La fecha de vencimiento debe ser posterior a la de inicio.');
        $connection->table('terceros_contratos')->where('id', $contractId)->update($data);

        return $this->contract($connection, $thirdPartyId, $contractId, $type);
    }

    public function deactivate(int $thirdPartyId, int $contractId, string $type, string $uuid): bool
    {
        $connection = $this->connection($uuid);
        if (!$this->contract($connection, $thirdPartyId, $contractId, $type)) {
            return false;
        }

        $connection->table('terceros_contratos')->where('id', $contractId)->update(['activo' => 0]);
        return true;
    }

    public function createPayment(int $thirdPartyId, int $contractId, string $type, int $userId, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        if (!$this->contract($connection, $thirdPartyId, $contractId, $type)) {
            return null;
        }
        abort_if($userId < 1 || !$connection->table('users')->where('id', $userId)->where('activo', 1)->exists(), 422, 'El usuario autenticado no está disponible.');

        $paymentId = $connection->table('terceros_contratos_pagos')->insertGetId([
            ...$data,
            'id_contrato' => $contractId,
            'id_usuario' => $userId,
            'activo' => 1,
        ]);

        return (array) $connection->table('terceros_contratos_pagos')
            ->select(['id', 'numero_factura', 'fecha_factura', 'valor', 'id_usuario'])
            ->where('id', $paymentId)
            ->first();
    }

    public function deactivatePayment(int $thirdPartyId, int $contractId, int $paymentId, string $type, string $uuid): bool
    {
        $connection = $this->connection($uuid);
        if (!$this->contract($connection, $thirdPartyId, $contractId, $type)) {
            return false;
        }

        return $connection->table('terceros_contratos_pagos')
            ->where('id', $paymentId)
            ->where('id_contrato', $contractId)
            ->where('activo', 1)
            ->update(['activo' => 0]) === 1;
    }

    public function formOptions(int $thirdPartyId, string $type, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $thirdParty = $connection->table('terceros')
            ->select(['id', 'razon_social', 'nombre_comercial'])
            ->where('id', $thirdPartyId)
            ->where('activo', 1)
            ->where($type, 'true')
            ->first();
        if (!$thirdParty) {
            return null;
        }

        return [
            'third_party' => (array) $thirdParty,
            'currencies' => $connection->table('monedas')->select(['id', 'nombre'])->where('activo', 1)->orderBy('nombre')->get()->map(fn ($row) => (array) $row)->all(),
            'states' => $connection->table('terceros_contratos_estados')->select(['id', 'nombre'])->where('activo', 1)->orderBy('nombre')->get()->map(fn ($row) => (array) $row)->all(),
            'payment_plans' => $connection->table('terceros_contratos_planes_pagos')->select(['id', 'nombre'])->where('activo', 1)->orderBy('nombre')->get()->map(fn ($row) => (array) $row)->all(),
            'users' => $connection->table('users')->select(['id', 'nombre', 'apellido'])->where('activo', 1)->orderBy('nombre')->orderBy('apellido')->get()->map(fn ($row) => (array) $row)->all(),
        ];
    }

    private function applySort($query, string $sort): void
    {
        $allowed = ['id', 'nombre', 'tipo_contrato', 'estado', 'fecha_inicio', 'fecha_vencimiento', 'monto'];
        $applied = false;

        foreach (array_filter(explode(',', $sort)) as $requested) {
            $column = ltrim($requested, '-');
            abort_unless(in_array($column, $allowed, true), 422, 'Campo de ordenamiento no permitido.');
            $query->orderBy($column, str_starts_with($requested, '-') ? 'desc' : 'asc');
            $applied = true;
        }

        if (!$applied) {
            $query->orderByDesc('fecha_vencimiento')->orderByDesc('id');
        }
    }

    private function thirdPartyExists(Connection $connection, int $thirdPartyId, string $type): bool
    {
        return $connection->table('terceros')
            ->where('id', $thirdPartyId)
            ->where('activo', 1)
            ->where($type, 'true')
            ->exists();
    }

    private function contract(Connection $connection, int $thirdPartyId, int $contractId, string $type): ?array
    {
        $contract = $connection->table('terceros_contratos')
            ->select([
                'id', 'id_tercero', 'nombre', 'tipo_contrato', 'estado', 'objeto_contrato',
                'id_moneda', 'monto', 'iva', 'id_responsable_ejecucion', 'fecha_inicio',
                'fecha_vencimiento', 'id_plan_pago', 'numero_pagos', 'id_responsable_pago',
                'nombre_responsable_pago', 'email_responsable_pago', 'renovacion_automatica',
                'observaciones', 'tipo',
            ])
            ->where('id', $contractId)
            ->where('id_tercero', $thirdPartyId)
            ->where('tipo', $type)
            ->where('activo', 1)
            ->first();

        return $contract ? (array) $contract : null;
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
