<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ImapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImapRuleController extends Controller
{
    public function __construct(private readonly ImapService $imapService)
    {
    }

    public function index(Request $request, int $account): JsonResponse
    {
        $rules = $this->imapService->rules((string) $request->header('x-uuid', ''), $account);
        abort_unless($rules !== null, 404, 'La cuenta IMAP no existe o está inactiva.');
        return response()->json($rules);
    }

    public function store(Request $request, int $account): JsonResponse
    {
        $rule = $this->imapService->createRule((string) $request->header('x-uuid', ''), $account, $this->validated($request));
        abort_unless($rule, 404, 'La cuenta IMAP no existe o está inactiva.');
        return response()->json($rule, 201);
    }

    public function update(Request $request, int $account, int $rule): JsonResponse
    {
        $updated = $this->imapService->updateRule((string) $request->header('x-uuid', ''), $account, $rule, $this->validated($request));
        abort_unless($updated, 404, 'La regla IMAP no existe, está inactiva o no pertenece a la cuenta.');
        return response()->json($updated);
    }

    public function destroy(Request $request, int $account, int $rule): JsonResponse
    {
        abort_unless($this->imapService->deactivateRule((string) $request->header('x-uuid', ''), $account, $rule), 404, 'La regla IMAP no existe, está inactiva o no pertenece a la cuenta.');
        return response()->json(['message' => 'Regla IMAP desactivada.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'palabra_clave' => ['required', 'string', 'max:50'],
            'tipo' => ['required', 'in:incidencia,problema,servicio'],
            'impacto' => ['required', 'integer', 'min:1'],
            'urgencia' => ['required', 'integer', 'min:1'],
            'id_area' => ['required', 'integer', 'min:1'],
            'id_categoria' => ['required', 'integer', 'min:1'],
            'id_subcategoria' => ['required', 'integer', 'min:1'],
            'asunto_default' => ['required', 'string', 'max:255'],
        ]);
    }
}
