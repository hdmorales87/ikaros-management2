<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function guardaPermisos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idRol' => ['required', 'integer'],
            'arrayPermisos' => ['present', 'array'],
            'arrayPermisos.*' => ['integer'],
        ]);
        $this->roleService->savePermissions(
            $data['idRol'],
            $data['arrayPermisos'],
            (string) $request->header('x-uuid', ''),
        );

        return response()->json(['msg' => 'success']);
    }

    public function permisos(Request $request, int $idRol): JsonResponse
    {
        return response()->json($this->roleService->permissions($idRol, (string) $request->header('x-uuid', '')));
    }

    public function managementData(Request $request): JsonResponse
    {
        return response()->json($this->roleService->managementData((string) $request->header('x-uuid', '')));
    }
}
