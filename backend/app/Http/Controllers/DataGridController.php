<?php

namespace App\Http\Controllers;

use App\Services\DataGridService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataGridController extends Controller
{
    public function __construct(private readonly DataGridService $dataGridService)
    {
    }

    public function getData(Request $request): JsonResponse
    {
        return response()->json($this->dataGridService->get($request, $this->uuid($request)));
    }

    public function insertData(Request $request): JsonResponse
    {
        return response()->json($this->dataGridService->insert($request, $this->uuid($request)), 201);
    }

    public function updateData(Request $request): JsonResponse
    {
        return response()->json($this->dataGridService->update($request, $this->uuid($request)));
    }

    public function deleteData(Request $request): JsonResponse
    {
        return response()->json($this->dataGridService->delete($request, $this->uuid($request)));
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
