<?php

namespace App\Http\Controllers;

use App\Services\ActivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivoController extends Controller
{
    public function __construct(private readonly ActivoService $activoService)
    {
    }

    public function generarCodigoActivo(Request $request): JsonResponse
    {
        $data = $request->validate(['idActivo' => ['required', 'integer']]);
        return response()->json($this->activoService->generateCode($data['idActivo'], $this->uuid($request)));
    }

    public function generarCodigosActivos(Request $request): JsonResponse
    {
        $data = $request->validate(['idCompra' => ['required', 'integer']]);
        return response()->json($this->activoService->generateCodes($data['idCompra'], $this->uuid($request)));
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
