<?php

namespace App\Http\Controllers;

use App\Services\FichaTecnicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FichaTecnicaController extends Controller
{
    public function __construct(private readonly FichaTecnicaService $fichaTecnicaService)
    {
    }

    public function guardarCamposFicha(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tabla' => ['required', 'string', 'in:activos,proyectos'],
            'idMaestro' => ['required', 'integer'],
            'arrayCampos' => ['present', 'array'],
        ]);
        $this->fichaTecnicaService->save($data['tabla'], $data['idMaestro'], $data['arrayCampos'], (string) $request->header('x-uuid', ''));
        return response()->json(['msg' => 'success']);
    }

    public function campos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tabla' => ['required', 'string', 'in:activos,proyectos'],
            'idMaestro' => ['required', 'integer'],
        ]);
        return response()->json($this->fichaTecnicaService->fields($data['tabla'], $data['idMaestro'], (string) $request->header('x-uuid', '')));
    }

    public function valores(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string', 'in:activos,proyectos'], 'idCampo' => ['required', 'integer']]);
        return response()->json($this->fichaTecnicaService->values($data['tabla'], $data['idCampo'], (string) $request->header('x-uuid', '')));
    }
}
