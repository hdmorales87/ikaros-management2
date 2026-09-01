<?php

namespace App\Http\Controllers;

use App\Services\TerceroService;
use App\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerceroController extends Controller
{
    public function __construct(private readonly TerceroService $terceroService, private readonly MailService $mailService)
    {
    }

    public function getEncuestaTercero(Request $request): JsonResponse
    {
        $data = $request->validate(['lastId' => ['required', 'integer'], 'idCliente' => ['required', 'integer']]);
        return response()->json($this->terceroService->survey($data['lastId'], $data['idCliente'], $this->uuid($request)));
    }

    public function indexClients(Request $request): JsonResponse
    {
        return $this->paginatedList($request, 'cliente');
    }

    public function indexProviders(Request $request): JsonResponse
    {
        return $this->paginatedList($request, 'proveedor');
    }

    public function getTerceroById(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer']]);
        $thirdParty = $this->terceroService->find($data['id'], $this->uuid($request));
        return $thirdParty ? response()->json([$thirdParty]) : response()->json(['msg' => 'not_found'], 404);
    }

    public function getPreguntasEncuestaTercero(Request $request): JsonResponse
    {
        return response()->json($this->terceroService->questions($this->uuid($request)));
    }

    public function guardarEncuestaTercero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idTercero' => ['required', 'integer'],
            'opcion' => ['required', 'string', 'in:cliente,proveedor'],
            'jsonRespuestas' => ['required', 'json'],
        ]);
        $answers = json_decode($data['jsonRespuestas'], true, 512, JSON_THROW_ON_ERROR);
        abort_unless(is_array($answers), 422, 'Formato de respuestas inválido.');
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new \App\Helpers\JwtAuth())->checkToken($token, true);
        $surveyId = $this->terceroService->saveSurvey($data['idTercero'], (int) ($identity->sub ?? 0), $data['opcion'], $answers, $this->uuid($request));
        return response()->json(['msg' => 'success', 'id' => $surveyId], 201);
    }

    public function linkEncuestaCliente(Request $request): JsonResponse
    {
        $data = $request->validate(['idCliente' => ['required', 'integer']]);
        $invitation = $this->terceroService->invitation($data['idCliente'], $this->uuid($request), (string) config('app.url'));
        if (($invitation['msg'] ?? null) === 'not_found') return response()->json($invitation, 404);
        $content = '<h1>Encuesta de satisfacción</h1><p>Hola '.e($invitation['name']).'. Te invitamos a compartir tu percepción sobre '.e($invitation['company']).'.</p><p><a href="'.e($invitation['link']).'">Diligenciar encuesta</a></p>';
        $this->mailService->send($invitation['email'], 'Encuesta de satisfacción', $content, $this->uuid($request));
        return response()->json(['msg' => 'success']);
    }

    private function paginatedList(Request $request, string $type): JsonResponse
    {
        $query = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
        ]);
        $method = $type === 'cliente' ? 'paginateClients' : 'paginateProviders';

        return response()->json($this->terceroService->{$method}(
            $this->uuid($request),
            trim((string) ($query['search'] ?? '')),
            (int) ($query['per_page'] ?? 25),
            (string) ($query['sort'] ?? ''),
        ));
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
