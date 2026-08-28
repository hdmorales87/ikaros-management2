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
}
