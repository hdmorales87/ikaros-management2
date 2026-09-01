<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InitiativeCommitteeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InitiativeCommitteeController extends Controller
{
    public function __construct(private readonly InitiativeCommitteeService $service) {}
    public function index(Request $request): JsonResponse { return response()->json($this->service->list((string) $request->header('x-uuid', ''))); }
    public function store(Request $request): JsonResponse { return response()->json($this->service->create((string) $request->header('x-uuid', ''), $request->validate(['id_iniciativa' => ['required', 'integer', 'min:1'], 'nombre' => ['required', 'string', 'max:255'], 'descripcion' => ['required', 'string'], 'orden' => ['required', 'integer', 'min:1']])), 201); }
    public function approvers(Request $request, int $committee): JsonResponse { $items = $this->service->approvers((string) $request->header('x-uuid', ''), $committee); abort_unless($items !== null, 404, 'El comité no existe o está inactivo.'); return response()->json($items); }
    public function addApprover(Request $request, int $committee): JsonResponse { $data = $request->validate(['id_user' => ['required', 'integer', 'min:1']]); $item = $this->service->addApprover((string) $request->header('x-uuid', ''), $committee, $data['id_user']); abort_unless($item, 404, 'El comité no existe o está inactivo.'); return response()->json($item, 201); }
    public function removeApprover(Request $request, int $committee, int $approver): JsonResponse { abort_unless($this->service->removeApprover((string) $request->header('x-uuid', ''), $committee, $approver), 404, 'El aprobador no existe o no pertenece al comité.'); return response()->json(['message' => 'Aprobador retirado.']); }
}
