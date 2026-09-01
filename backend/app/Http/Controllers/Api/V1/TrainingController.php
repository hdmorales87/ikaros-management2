<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function __construct(private readonly TrainingService $trainingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->trainingService->list((string) $request->header('x-uuid', '')));
    }

    public function show(Request $request, int $training): JsonResponse
    {
        $item = $this->trainingService->find((string) $request->header('x-uuid', ''), $training);
        abort_unless($item, 404, 'La capacitación no existe o está inactiva.');
        return response()->json($item);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->trainingService->create((string) $request->header('x-uuid', ''), $this->trainingData($request, true)), 201);
    }

    public function update(Request $request, int $training): JsonResponse
    {
        $item = $this->trainingService->update((string) $request->header('x-uuid', ''), $training, $this->trainingData($request, false));
        abort_unless($item, 404, 'La capacitación no existe o está inactiva.');
        return response()->json($item);
    }

    public function destroy(Request $request, int $training): JsonResponse
    {
        abort_unless($this->trainingService->deactivate((string) $request->header('x-uuid', ''), $training), 404, 'La capacitación no existe o está inactiva.');
        return response()->json(['message' => 'Capacitación desactivada.']);
    }

    public function attendees(Request $request, int $training): JsonResponse
    {
        $items = $this->trainingService->attendees((string) $request->header('x-uuid', ''), $training);
        abort_unless($items !== null, 404, 'La capacitación no existe o está inactiva.');
        return response()->json($items);
    }

    public function addAttendee(Request $request, int $training): JsonResponse
    {
        $data = $request->validate(['id_usuario' => ['required', 'integer', 'min:1']]);
        $item = $this->trainingService->addAttendee((string) $request->header('x-uuid', ''), $training, $data['id_usuario']);
        abort_unless($item, 404, 'La capacitación no existe o está inactiva.');
        return response()->json($item, 201);
    }

    public function updateAttendance(Request $request, int $training, int $attendee): JsonResponse
    {
        $data = $request->validate(['asistencia' => ['required', 'boolean']]);
        $item = $this->trainingService->updateAttendance((string) $request->header('x-uuid', ''), $training, $attendee, $data['asistencia']);
        abort_unless($item, 404, 'El asistente no existe o no pertenece a la capacitación.');
        return response()->json($item);
    }

    public function removeAttendee(Request $request, int $training, int $attendee): JsonResponse
    {
        abort_unless($this->trainingService->removeAttendee((string) $request->header('x-uuid', ''), $training, $attendee), 404, 'El asistente no existe o no pertenece a la capacitación.');
        return response()->json(['message' => 'Asistente retirado.']);
    }

    private function trainingData(Request $request, bool $creating): array
    {
        $optional = $creating ? ['nullable'] : ['sometimes', 'nullable'];
        $rules = [
            'nombre' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'instructor' => [...$optional, 'string', 'max:255'],
            'intensidad' => [...$optional, 'integer', 'min:1'],
            'fecha_inicio' => [...$optional, 'date'],
            'hora_inicio' => [...$optional, 'date_format:H:i'],
            'fecha_final' => [...$optional, 'date'],
            'hora_final' => [...$optional, 'date_format:H:i'],
            'lugar' => [...$optional, 'string', 'max:255'],
            'observaciones' => [...$optional, 'string', 'max:5000'],
        ];
        return $request->validate($rules);
    }
}
