<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class ActivoService
{
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

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
