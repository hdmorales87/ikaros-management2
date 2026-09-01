<?php

namespace App\Http\Controllers;

use App\Services\SolicitudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function __construct(private readonly SolicitudService $solicitudService)
    {
    }

    public function getSolicitudesUrgencias(Request $request): JsonResponse
    {
        return response()->json($this->solicitudService->urgencyOptions($this->uuid($request)));
    }

    public function indexIncidents(Request $request): JsonResponse
    {
        return $this->paginatedRequests($request, 'incidencia');
    }

    public function indexProblems(Request $request): JsonResponse
    {
        return $this->paginatedRequests($request, 'problema');
    }

    public function indexServices(Request $request): JsonResponse
    {
        return $this->paginatedRequests($request, 'servicio');
    }

    public function showIncident(Request $request, int $incident): JsonResponse
    {
        return $this->requestDetail($this->solicitudService->findIncident($incident, $this->uuid($request)));
    }

    public function incidentFollowups(Request $request, int $incident): JsonResponse
    {
        return response()->json($this->solicitudService->incidentFollowups($incident, $this->uuid($request)));
    }

    public function showService(Request $request, int $service): JsonResponse
    {
        return $this->requestDetail($this->solicitudService->findService($service, $this->uuid($request)));
    }

    public function serviceFollowups(Request $request, int $service): JsonResponse
    {
        return response()->json($this->solicitudService->serviceFollowups($service, $this->uuid($request)));
    }

    public function assignIncident(Request $request, int $incident): JsonResponse
    {
        return response()->json($this->solicitudService->assign($incident, 'incidencia', $this->uuid($request)));
    }

    public function startIncident(Request $request, int $incident): JsonResponse
    {
        return response()->json($this->solicitudService->startProcessing($incident, 'incidencia', $this->jwtUserId($request), $this->uuid($request)));
    }

    public function assignProblem(Request $request, int $problem): JsonResponse
    {
        return response()->json($this->solicitudService->assign($problem, 'problema', $this->uuid($request)));
    }

    public function startProblem(Request $request, int $problem): JsonResponse
    {
        return response()->json($this->solicitudService->startProcessing($problem, 'problema', $this->jwtUserId($request), $this->uuid($request)));
    }

    public function assignService(Request $request, int $service): JsonResponse
    {
        return response()->json($this->solicitudService->assign($service, 'servicio', $this->uuid($request)));
    }

    public function startService(Request $request, int $service): JsonResponse
    {
        return response()->json($this->solicitudService->startProcessing($service, 'servicio', $this->jwtUserId($request), $this->uuid($request)));
    }

    private function paginatedRequests(Request $request, string $type): JsonResponse
    {
        $query = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
        ]);

        $method = match ($type) {
            'incidencia' => 'paginateIncidents',
            'problema' => 'paginateProblems',
            'servicio' => 'paginateServices',
        };

        return response()->json($this->solicitudService->{$method}(
            $this->uuid($request),
            trim((string) ($query['search'] ?? '')),
            (int) ($query['per_page'] ?? 25),
            (string) ($query['sort'] ?? ''),
        ));
    }

    private function requestDetail(?array $request): JsonResponse
    {
        return $request
            ? response()->json($request)
            : response()->json(['message' => 'Solicitud no encontrada.'], 404);
    }

    public function getSolicitudesImpactos(Request $request): JsonResponse
    {
        return response()->json($this->solicitudService->impactOptions($this->uuid($request)));
    }

    public function getAreasServicioByModulo(Request $request, string $modulo): JsonResponse
    {
        return response()->json($this->solicitudService->areasByModule($this->uuid($request), $modulo));
    }

    public function getCategoriasByAreaServicio(Request $request, int $area): JsonResponse
    {
        return response()->json($this->solicitudService->categoriesByArea($this->uuid($request), $area));
    }

    public function getSubcategoriasByCategoria(Request $request, int $categoria): JsonResponse
    {
        return response()->json($this->solicitudService->subcategoriesByCategory($this->uuid($request), $categoria));
    }

    public function guardarSolicitud(Request $request): JsonResponse
    {
        $userId = (int) ($request->user()?->id ?? $this->jwtUserId($request));
        $requestData = $this->solicitudService->create($request->all(), $this->uuid($request), $userId);

        return response()->json(['msg' => 'success', ...$requestData], 201);
    }

    public function verificarEstadoSolicitud(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string'], 'id' => ['required', 'integer']]);
        return response()->json($this->solicitudService->status($data['tabla'], $data['id'], $this->uuid($request)));
    }

    public function rechazarSolucion(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string'], 'idRow' => ['required', 'integer'], 'observacion' => ['required', 'string', 'max:5000']]);
        $identity = $this->identity($request);
        $this->solicitudService->reject($data['tabla'], $data['idRow'], $data['observacion'], (int) ($identity->sub ?? 0), $this->uuid($request));
        return response()->json(['msg' => 'success']);
    }

    public function recategorizacionIncidencia(Request $request): JsonResponse
    {
        $data = $request->validate(['idRow' => ['required', 'integer'], 'id_area' => ['required', 'integer'], 'id_categoria' => ['required', 'integer'], 'id_subcategoria' => ['required', 'integer'], 'observacion' => ['required', 'string', 'max:5000']]);
        $identity = $this->identity($request);
        $this->solicitudService->recategorize($data['idRow'], $data['id_area'], $data['id_categoria'], $data['id_subcategoria'], $data['observacion'], (int) ($identity->sub ?? 0), $this->uuid($request));
        return response()->json(['msg' => 'success']);
    }

    public function procesoGestion(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string'], 'opcion' => ['required', 'string'], 'idRow' => ['required', 'integer'], 'estado' => ['required', 'integer', 'between:0,6'], 'estadoActual' => ['sometimes', 'integer', 'between:0,6'], 'name_estado' => ['required', 'string', 'max:100'], 'observacion' => ['required', 'string', 'max:5000'], 'idAsignado' => ['sometimes', 'integer'], 'causaProblema' => ['sometimes', 'string', 'max:1000']]);
        $identity = $this->identity($request);
        return response()->json($this->solicitudService->manage($data, (int) ($identity->sub ?? 0), $this->uuid($request)));
    }

    public function asignarSolicitud(Request $request): JsonResponse
    {
        $data = $request->validate(['idRow' => ['required', 'integer'], 'tipo' => ['required', 'string', 'in:incidencia,problema,servicio']]);
        return response()->json($this->solicitudService->assign($data['idRow'], $data['tipo'], $this->uuid($request)));
    }

    public function verificarEncuesta(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string'], 'idSolicitud' => ['required', 'integer']]);
        return response()->json($this->solicitudService->surveyStatus($data['tabla'], $data['idSolicitud'], $this->uuid($request)));
    }

    public function guardarEncuesta(Request $request): JsonResponse
    {
        $data = $request->validate(['tabla' => ['required', 'string'], 'idSolicitud' => ['required', 'integer'], 'jsonRespuestas' => ['required', 'json']]);
        $answers = json_decode($data['jsonRespuestas'], true, 512, JSON_THROW_ON_ERROR);
        abort_unless(is_array($answers), 422, 'Formato de encuesta inválido.');
        return response()->json($this->solicitudService->saveSurvey($data['tabla'], $data['idSolicitud'], $answers, $this->uuid($request)));
    }

    public function getEstadoSolicitudValidacion(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer']]);
        return response()->json($this->solicitudService->validationStatus($data['id'], $this->uuid($request)));
    }

    public function guardarValidacionIniciativa(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => ['required', 'integer'], 'data' => ['required', 'array']]);
        $this->solicitudService->saveValidation($data['id'], $data['data'], $this->uuid($request));
        return response()->json(['msg' => 'success']);
    }

    public function guardarValidacionIniciativaSeguimiento(Request $request): JsonResponse
    {
        $data = $request->validate(['data' => ['required', 'array']]);
        $this->solicitudService->saveValidationFollowup($data['data'], $this->uuid($request));
        return response()->json(['msg' => 'success']);
    }

    private function identity(Request $request): object
    {
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new \App\Helpers\JwtAuth())->checkToken($token, true);
        return is_object($identity) ? $identity : (object) [];
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }

    private function jwtUserId(Request $request): int
    {
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new \App\Helpers\JwtAuth())->checkToken($token, true);

        return (int) ($identity->sub ?? 0);
    }
}
