<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function checkUsername(Request $request): JsonResponse
    {
        return app(AuthController::class)->checkUsername($request);
    }

    public function login(Request $request): JsonResponse
    {
        return app(AuthController::class)->login($request);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        return app(AuthController::class)->updatePassword($request);
    }

    public function checkUserToken(Request $request): JsonResponse
    {
        return app(AuthController::class)->checkUserToken($request);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->userService->listUsers($this->uuid($request)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findUser($id, $this->uuid($request));

        return $user
            ? response()->json($user)
            : response()->json(['msg' => 'not_found'], 404);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
            'id_rol' => ['required', 'integer'],
            'activo' => ['sometimes', 'boolean'],
            'acceso_sistema' => ['sometimes', 'boolean'],
        ]);

        return response()->json($this->userService->createUser($data, $this->uuid($request)), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'apellido' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:150'],
            'password' => ['sometimes', 'string', 'min:6'],
            'id_rol' => ['sometimes', 'integer'],
            'activo' => ['sometimes', 'boolean'],
            'acceso_sistema' => ['sometimes', 'boolean'],
        ]);
        $user = $this->userService->updateUser($id, $data, $this->uuid($request));

        return $user
            ? response()->json($user)
            : response()->json(['msg' => 'not_found'], 404);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->userService->deleteUser($id, $this->uuid($request))
            ? response()->json(['msg' => 'success'])
            : response()->json(['msg' => 'not_found'], 404);
    }

    private function uuid(Request $request): string
    {
        return (string) ($request->header('x-uuid') ?: '');
    }
}
