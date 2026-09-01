<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InitiativeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InitiativeController extends Controller
{
    public function __construct(private readonly InitiativeService $initiativeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->initiativeService->list((string) $request->header('x-uuid', '')));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->initiativeService->create((string) $request->header('x-uuid', ''), $this->validated($request)), 201);
    }

    public function update(Request $request, int $initiative): JsonResponse
    {
        $item = $this->initiativeService->update((string) $request->header('x-uuid', ''), $initiative, $this->validated($request));
        abort_unless($item, 404, 'La iniciativa no existe o está inactiva.');
        return response()->json($item);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nomenclatura' => ['required', 'string', 'max:255'], 'nombre' => ['required', 'string', 'max:255'],
            'id_departamento' => ['required', 'integer', 'min:1'], 'id_propietario' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'], 'hora_inicio' => ['required', 'date_format:H:i'],
            'fecha_final' => ['required', 'date'], 'hora_final' => ['required', 'date_format:H:i'],
            'presupuesto' => ['required', 'numeric', 'min:0'], 'tiempo' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string'], 'beneficios_cualitativos' => ['required', 'string'],
            'beneficios_cuantitativos' => ['required', 'string'], 'escenario_pesimista' => ['required', 'string'],
            'escenario_optimista' => ['required', 'string'],
        ]);
    }
}
