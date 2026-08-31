<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function checkUsername(Request $request): JsonResponse
    {
        $username = $request->route('username', $request->input('username'));
        $validator = Validator::make(['username' => $username], [
            'username' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $uuid = $request->header('x-uuid');
        $result = $this->userService->checkUsername($username, $uuid);

        return response()->json($result);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $authHeader = $request->header('Authorization');
            
            if (!$authHeader) {
                return response()->json([
                    'msg' => 'missing_header',
                    'detail' => 'Header Authorization no presente'
                ], 400);
            }

            $loginHeader = substr($authHeader, 6);
            $loginHeader = base64_decode($loginHeader);
            $credentials = explode(':', $loginHeader);

            if (!is_array($credentials) || count($credentials) !== 2) {
                return response()->json([
                    'msg' => 'invalid_header',
                    'detail' => 'Formato de header inválido'
                ], 400);
            }

            $uuid = $request->header('x-uuid');
            $userData = $this->userService->login($credentials[0], $credentials[1], $uuid);

            if (isset($userData['code'])) {
                return response()->json($userData, $userData['code']);
            }

            if (!isset($userData['id'])) {
                return response()->json($userData, 401);
            }

            $company = new \App\Models\Company();
            $companyData = $company->getCompanyByUUID($uuid);

            if (!$companyData) {
                return response()->json([
                    'msg' => 'company_not_found',
                    'detail' => 'Empresa no encontrada'
                ], 404);
            }

            $result = $this->buildLoginResponse($userData, $companyData, $uuid);

            $jwtAuth = new \App\Helpers\JwtAuth();
            $token = $jwtAuth->signup($result);

            return response()->json($token, 200);
        } catch (\Exception $e) {
            \Log::error("|AuthController|login: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    private function buildLoginResponse(array $userData, object $companyData, string $uuid): object
    {
        $result = new \stdClass();

        unset($companyData->id);
        unset($companyData->fecha_vencimiento_licencia);

        $companyData->id_local = (int) $companyData->id_local;
        $companyData->maximo_usuarios = (int) $companyData->maximo_usuarios;
        $companyData->cuota_almacenamiento = (int) $companyData->cuota_almacenamiento;
        $companyData->dias_vencimiento = (int) $companyData->dias_vencimiento;

        $result->companyData = $companyData;

        $sensitiveFields = [
            'id_tipo_documento', 'documento', 'segundo_nombre', 'segundo_apellido',
            'nombre', 'password', 'fecha_cambio_password', 'direccion', 'telefono',
            'token', 'activo', 'acceso_sistema', 'intentos_login', 'fecha_nacimiento'
        ];

        foreach ($sensitiveFields as $field) {
            unset($userData[$field]);
        }

        $userData['id'] = (int) $userData['id'];
        $userData['id_rol'] = (int) $userData['id_rol'];
        $userData['id_departamento'] = (int) $userData['id_departamento'];
        $userData['id_area'] = (int) $userData['id_area'];
        $userData['idioma'] = (int) $userData['idioma'];
        $userData['sexo'] = (int) $userData['sexo'];
        $userData['dias_cambio'] = (int) $userData['dias_cambio'];

        $result->userData = (object) $userData;
        $result->is_superuser = $userData['id_rol'] === 1;

        $role = new \App\Models\Role();
        $permisos = $role->getPermisosByRole($userData['id_rol'], $uuid);

        $strPermisos = implode(',', array_column($permisos, 'id_permiso'));
        $result->permisos = $strPermisos;

        $modulos = \DB::connection('global')
            ->table('companies_modulos as m')
            ->select('id_modulo')
            ->join('companies as com', 'com.id', '=', 'm.id_empresa')
            ->where('com.uuid', $uuid)
            ->orderBy('m.id_modulo', 'asc')
            ->get();

        $strModulos = implode(',', array_column($modulos->toArray(), 'id_modulo'));
        $result->modulos = $strModulos;

        return $result;
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'token' => 'required|string',
            'opcion' => 'required|string|in:reset,force',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->userService->updatePassword($request);

        return response()->json($result);
    }

    public function checkUserToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->userService->checkUserToken($request);

        return response()->json($result);
    }
}
