<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ActivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivoController extends Controller
{
    public function __construct(private readonly ActivoService $activoService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($this->activoService->paginate(
            (string) $request->header('x-uuid', ''),
            trim((string) ($query['search'] ?? '')),
            (int) ($query['per_page'] ?? 25),
            (string) ($query['sort'] ?? ''),
        ));
    }

    public function show(Request $request, int $asset): JsonResponse
    {
        $result = $this->activoService->find($asset, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Activo no encontrado.'], 404);
    }

    public function update(Request $request, int $asset): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'id_tipo' => ['nullable', 'integer', 'min:1'],
            'id_departamento' => ['nullable', 'integer', 'min:1'],
            'id_proveedor' => ['sometimes', 'integer', 'min:1'],
            'estado' => ['nullable', 'integer', 'min:1'],
            'id_asignado' => ['nullable', 'integer', 'min:1'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'fecha_compra' => ['nullable', 'date'],
            'numero_factura' => ['nullable', 'string', 'max:100'],
            'id_ubicacion' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $this->activoService->update($asset, $data, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Activo no encontrado.'], 404);
    }
}