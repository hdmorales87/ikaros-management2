<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function risk(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer']]);
        return response()->json($this->notificationService->risk($data['id'], (string) $request->header('x-uuid', '')));
    }

    public function activity(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer'], 'id_user' => ['required', 'integer'], 'tipo' => ['required', 'string', 'in:principal,adicional']]);
        $identity = (new \App\Helpers\JwtAuth())->checkToken(str_replace('Bearer ', '', (string) $request->header('Authorization')), true);
        return response()->json($this->notificationService->activity($data['id'], $data['tipo'], (int) ($identity->sub ?? $data['id_user']), (string) $request->header('x-uuid', '')));
    }

    public function tableChanges(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer'], 'titulo' => ['required', 'string', 'max:100'], 'tabla' => ['required', 'string'], 'seccion_tabla' => ['required', 'integer']]);
        $identity = (new \App\Helpers\JwtAuth())->checkToken(str_replace('Bearer ', '', (string) $request->header('Authorization')), true);
        return response()->json($this->notificationService->tableChanges($data, (int) ($identity->sub ?? 0), (string) $request->header('x-uuid', '')));
    }

    public function committee(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer'], 'idUser' => ['required', 'integer']]);
        return response()->json($this->notificationService->committee($data['id'], $data['idUser'], (string) $request->header('x-uuid', '')));
    }

    public function initiative(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer']]);
        return response()->json($this->notificationService->initiative($data['id'], (string) $request->header('x-uuid', '')));
    }

    public function training(Request $request): JsonResponse
    {
        $data = $request->validate(['idRow' => ['required', 'integer']]);
        return response()->json($this->notificationService->training($data['idRow'], (string) $request->header('x-uuid', '')));
    }

    public function hours(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer'], 'tipo' => ['required', 'string', 'in:planeadas,ejecutadas']]);
        return response()->json($this->notificationService->hours($data['id'], $data['tipo'], (string) $request->header('x-uuid', '')));
    }

    public function confirmedHours(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => ['required'], 'tipo' => ['required', 'string', 'in:planeadas,ejecutadas']]);
        $ids = is_string($data['ids']) ? json_decode($data['ids'], true, 512, JSON_THROW_ON_ERROR) : $data['ids'];
        abort_unless(is_array($ids), 422, 'Lista de horas inválida.');
        $identity = (new \App\Helpers\JwtAuth())->checkToken(str_replace('Bearer ', '', (string) $request->header('Authorization')), true);
        return response()->json($this->notificationService->confirmedHours(array_map('intval', $ids), $data['tipo'], (int) ($identity->sub ?? 0), (string) $request->header('x-uuid', '')));
    }
}
