<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ThirdPartySurveyQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThirdPartySurveyQuestionController extends Controller
{
    public function __construct(private readonly ThirdPartySurveyQuestionService $questionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => ['required', 'in:cliente,proveedor']]);
        return response()->json($this->questionService->list((string) $request->header('x-uuid', ''), $data['type']));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json($this->questionService->create((string) $request->header('x-uuid', ''), $this->validated($request)), 201);
    }

    public function update(Request $request, int $question): JsonResponse
    {
        $updated = $this->questionService->update((string) $request->header('x-uuid', ''), $question, $this->validated($request));
        abort_unless($updated, 404, 'La pregunta no existe o está inactiva.');
        return response()->json($updated);
    }

    public function destroy(Request $request, int $question): JsonResponse
    {
        abort_unless($this->questionService->deactivate((string) $request->header('x-uuid', ''), $question), 404, 'La pregunta no existe o está inactiva.');
        return response()->json(['message' => 'Pregunta desactivada.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tipo' => ['required', 'in:cliente,proveedor'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);
    }
}
