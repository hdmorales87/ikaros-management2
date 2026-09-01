<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService)
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

        return response()->json($this->projectService->paginate(
            (string) $request->header('x-uuid', ''),
            trim((string) ($query['search'] ?? '')),
            (int) ($query['per_page'] ?? 25),
            (string) ($query['sort'] ?? ''),
        ));
    }

    public function update(Request $request, int $project): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_final' => ['sometimes', 'date'],
        ]);
        $result = $this->projectService->update($project, $data, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Proyecto no encontrado.'], 404);
    }

    public function storeActivity(Request $request, int $project): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'fecha_inicio' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'fecha_final' => ['required', 'date'],
            'hora_final' => ['required', 'date_format:H:i'],
        ]);
        $result = $this->projectService->createActivity($project, $data, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result, 201)
            : response()->json(['message' => 'Proyecto no encontrado.'], 404);
    }

    public function storeRisk(Request $request, int $project): JsonResponse
    {
        $data = $request->validate(['nombre' => ['required', 'string', 'max:255']]);
        $result = $this->projectService->createRisk($project, $data['nombre'], (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result, 201)
            : response()->json(['message' => 'Proyecto no encontrado.'], 404);
    }
    public function activities(Request $request, int $project): JsonResponse
    {
        $result = $this->projectService->activities($project, (string) $request->header('x-uuid', ''));

        return $result !== null
            ? response()->json($result)
            : response()->json(['message' => 'Proyecto no encontrado.'], 404);
    }

    public function risks(Request $request, int $project): JsonResponse
    {
        $result = $this->projectService->risks($project, (string) $request->header('x-uuid', ''));

        return $result !== null
            ? response()->json($result)
            : response()->json(['message' => 'Proyecto no encontrado.'], 404);
    }
}