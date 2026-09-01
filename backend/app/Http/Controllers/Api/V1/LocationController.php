<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->locationService->list((string) $request->header('x-uuid', '')));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->locationService->create((string) $request->header('x-uuid', ''), $this->validated($request)), 201);
    }

    public function update(Request $request, int $location): JsonResponse
    {
        $updated = $this->locationService->update((string) $request->header('x-uuid', ''), $location, $this->validated($request));
        abort_unless($updated, 404, 'La ubicación no existe o está inactiva.');
        return response()->json($updated);
    }

    public function destroy(Request $request, int $location): JsonResponse
    {
        abort_unless($this->locationService->deactivate((string) $request->header('x-uuid', ''), $location), 404, 'La ubicación no existe o está inactiva.');
        return response()->json(['message' => 'Ubicación desactivada.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'id_departamento' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);
    }
}
