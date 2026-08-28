<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Services\ConocimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConocimientoController extends Controller
{
    public function __construct(private readonly ConocimientoService $conocimientoService)
    {
    }

    public function getConocimientos(Request $request): JsonResponse
    {
        return response()->json($this->conocimientoService->list($this->uuid($request)));
    }

    public function getConocimientosByDescripcion(Request $request): JsonResponse
    {
        $data = $request->validate(['tema' => ['nullable', 'string', 'max:255']]);

        return response()->json($this->conocimientoService->list($this->uuid($request), $data['tema'] ?? null));
    }

    public function getConocimientoById(Request $request, int $id): JsonResponse
    {
        $knowledge = $this->conocimientoService->find($id, $this->uuid($request));

        return $knowledge
            ? response()->json([$knowledge])
            : response()->json(['msg' => 'not_found'], 404);
    }

    public function guardarReputacionConocimiento(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idRow' => ['required', 'integer'],
            'puntaje' => ['required', 'integer', 'between:1,5'],
        ]);
        $identity = $this->identity($request);
        $result = $this->conocimientoService->rate(
            $data['idRow'],
            (int) ($identity->sub ?? 0),
            $data['puntaje'],
            $this->uuid($request),
        );

        return response()->json(['msg' => $result]);
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }

    private function identity(Request $request): object
    {
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new JwtAuth())->checkToken($token, true);

        return is_object($identity) ? $identity : (object) [];
    }
}
