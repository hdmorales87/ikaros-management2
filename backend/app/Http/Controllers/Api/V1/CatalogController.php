<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalogService)
    {
    }

    public function index(Request $request, string $catalog): JsonResponse
    {
        return response()->json($this->catalogService->list($catalog, $this->uuid($request)));
    }

    public function store(Request $request, string $catalog): JsonResponse
    {
        $data = $request->validate($this->catalogService->rules($catalog, true));
        return response()->json($this->catalogService->create($catalog, $data, $this->uuid($request)), 201);
    }

    public function update(Request $request, string $catalog, int $item): JsonResponse
    {
        $data = $request->validate($this->catalogService->rules($catalog, false));
        $result = $this->catalogService->update($catalog, $item, $data, $this->uuid($request));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Registro no encontrado.'], 404);
    }

    public function deactivate(Request $request, string $catalog, int $item): JsonResponse
    {
        $deactivated = $this->catalogService->deactivate($catalog, $item, $this->uuid($request));

        return $deactivated
            ? response()->json(['message' => 'Registro desactivado.'])
            : response()->json(['message' => 'Registro no encontrado.'], 404);
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
