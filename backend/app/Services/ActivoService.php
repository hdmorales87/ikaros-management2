<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class ActivoService
{
    private const DETAIL_COLUMNS = [
        'id', 'nombre', 'codigo', 'marca', 'id_tipo', 'id_departamento',
        'id_proveedor', 'estado', 'id_asignado', 'precio_compra',
        'fecha_compra', 'numero_factura', 'id_ubicacion', 'activo',
    ];

    public function paginate(string $uuid, string $search, int $perPage, string $sort): array
    {
        $connection = $this->connection($uuid);
        $query = $connection->table('activos')
            ->select(['id', 'codigo', 'nombre', 'marca', 'activo'])
            ->where('activo', 1);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%')
                    ->orWhere('marca', 'like', '%'.$search.'%');
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

    public function find(int $assetId, string $uuid): ?array
    {
        $asset = $this->connection($uuid)->table('activos')
            ->select(self::DETAIL_COLUMNS)
            ->where('id', $assetId)
            ->where('activo', 1)
            ->first();

        return $asset ? (array) $asset : null;
    }

    public function formOptions(string $uuid): array
    {
        $connection = $this->connection($uuid);

        return [
            'types' => $this->activeOptions($connection, 'activos_tipos'),
            'departments' => $this->activeOptions($connection, 'departamentos'),
            'providers' => $connection->table('terceros')
                ->select(['id', 'razon_social as nombre'])
                ->where('activo', 1)
                ->orderBy('razon_social')
                ->get()
                ->map(fn ($provider) => (array) $provider)
                ->all(),
            'states' => $this->activeOptions($connection, 'activos_estados'),
            'users' => $connection->table('users')
                ->select(['id', 'primer_nombre as nombre', 'primer_apellido as apellido'])
                ->where('activo', 1)
                ->orderBy('primer_nombre')
                ->get()
                ->map(fn ($user) => (array) $user)
                ->all(),
        ];
    }

    public function update(int $assetId, array $data, string $uuid): ?array
    {
        $connection = $this->connection($uuid);
        $updated = $connection->table('activos')
            ->where('id', $assetId)
            ->where('activo', 1)
            ->update($data);

        return $updated > 0 || $this->find($assetId, $uuid) ? $this->find($assetId, $uuid) : null;
    }

    public function generateCode(int $assetId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        return $connection->transaction(function () use ($connection, $assetId): array {
            $asset = $connection->table('activos')->where('id', $assetId)->first(['id_tipo', 'codigo']);
            if (!$asset || $asset->codigo) return ['msg' => 'no_change'];
            $code = $this->nextCode($connection, (int) $asset->id_tipo, $assetId);
            $connection->table('activos')->where('id', $assetId)->update(['codigo' => $code]);
            return ['msg' => 'success', 'detail' => $code];
        });
    }

    public function generateCodes(int $purchaseId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        return $connection->transaction(function () use ($connection, $purchaseId): array {
            if ($connection->table('activos')->where('id_compra', $purchaseId)->exists()) return ['msg' => 'no_change'];
            $purchase = $connection->table('activos_compras')->where('id', $purchaseId)->first();
            if (!$purchase) return ['msg' => 'no_change'];
            $rows = [];
            for ($index = 0; $index < min(100, (int) $purchase->cantidad); $index++) {
                $rows[] = ['id_departamento' => $purchase->id_departamento, 'id_ubicacion' => $purchase->id_ubicacion, 'id_tipo' => $purchase->id_tipo_activo, 'nombre' => $purchase->nombre_activo, 'id_proveedor' => $purchase->id_proveedor, 'marca' => $purchase->marca, 'precio_compra' => $purchase->precio_compra_unitario, 'fecha_compra' => $purchase->fecha_compra, 'numero_factura' => $purchase->numero_factura, 'id_compra' => $purchaseId];
            }
            if ($rows !== []) $connection->table('activos')->insert($rows);
            foreach ($connection->table('activos')->where('id_compra', $purchaseId)->get(['id', 'id_tipo', 'codigo']) as $asset) {
                if (!$asset->codigo) $connection->table('activos')->where('id', $asset->id)->update(['codigo' => $this->nextCode($connection, (int) $asset->id_tipo, (int) $asset->id)]);
            }
            return ['msg' => 'success'];
        });
    }

    private function nextCode(Connection $connection, int $typeId, int $assetId): string
    {
        $previous = $connection->table('activos')->whereNotNull('codigo')->where('id_tipo', $typeId)->where('id', '<', $assetId)->orderByDesc('id')->value('codigo');
        $number = $previous ? ((int) substr((string) $previous, 2) + 1) : 1;
        return str_pad((string) $typeId, 2, '0', STR_PAD_LEFT).str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function applySort($query, string $sort): void
    {
        $allowed = ['id', 'codigo', 'nombre', 'marca'];
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

    private function activeOptions(Connection $connection, string $table): array
    {
        return $connection->table($table)
            ->select(['id', 'nombre'])
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($option) => (array) $option)
            ->all();
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
