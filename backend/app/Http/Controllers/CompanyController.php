<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    public function sincronizarEmpresa(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documento' => ['required', 'string'],
            'data' => ['required', 'array'],
            'data.tipo_licencia' => ['required'],
            'data.fecha_vencimiento_licencia' => ['required', 'date'],
            'data.maximo_usuarios' => ['required', 'integer'],
            'data.cuota_almacenamiento' => ['required', 'integer'],
        ]);

        return response()->json($this->companyService->sincronizarEmpresa((object) $data));
    }

    public function guardaModulos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idRow' => ['required', 'integer'],
            'arrayModulos' => ['present', 'array'],
            'arrayModulos.*' => ['integer'],
        ]);

        return response()->json($this->companyService->guardaModulos((object) $data));
    }

    public function crearDirectorios(Request $request): JsonResponse
    {
        $data = $request->validate(['idEmpresa' => ['required', 'integer']]);

        return response()->json($this->companyService->crearDirectorios((object) $data));
    }

    public function getChatbotMenu(Request $request): JsonResponse
    {
        return response()->json($this->companyService->chatbotMenu((string) $request->header('x-uuid', '')));
    }

    public function checkCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'documento' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = $this->companyService->checkCompany($request->documento);

            return response()->json($company);
        } catch (\Exception $e) {
            \Log::error("|CompanyController|checkCompany: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getCompanyData(Request $request): JsonResponse
    {
        try {
            $token = substr($request->header('Authorization'), 7);
            $jwtAuth = new \App\Helpers\JwtAuth();
            $checkToken = $jwtAuth->checkToken($token, true);

            $company = Company::where('documento', $checkToken->companyData->documento)
                ->where('activo', 1)
                ->select([
                    'id',
                    'razon_social',
                    'id_local',
                    'tipo_licencia',
                    'fecha_vencimiento_licencia',
                    'maximo_usuarios',
                    'cuota_almacenamiento',
                    'uuid',
                ])
                ->first();

            if ($company) {
                $company->dias = \DB::raw("DATEDIFF(fecha_vencimiento_licencia,NOW()) AS dias");
            }

            return response()->json($company);
        } catch (\Exception $e) {
            \Log::error("|CompanyController|getCompanyData: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getCompanyModules(Request $request): JsonResponse
    {
        try {
            $uuid = $request->header('x-uuid');
            
            if ($request->hasHeader('Authorization')) {
                $token = substr($request->header('Authorization'), 7);
                $jwtAuth = new \App\Helpers\JwtAuth();
                $checkToken = $jwtAuth->checkToken($token, true);
                $uuid = $checkToken->companyData->uuid;
            }

            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);

            $modulos = $connection->table('companies_modulos as m')
                ->select('id_modulo')
                ->join('companies as com', 'com.id', '=', 'm.id_empresa')
                ->where('com.uuid', $uuid)
                ->orderBy('m.id_modulo', 'asc')
                ->get();

            return response()->json($modulos);
        } catch (\Exception $e) {
            \Log::error("|CompanyController|getCompanyModules: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getPoliticasSeguridad(Request $request): JsonResponse
    {
        try {
            $uuid = $request->header('x-uuid');
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);

            $politicas = $connection->table('politicas_seguridad')
                ->select('*')
                ->where('activo', 1)
                ->get();

            return response()->json($politicas);
        } catch (\Exception $e) {
            \Log::error("|CompanyController|getPoliticasSeguridad: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getIdioma(Request $request): JsonResponse
    {
        try {
            $uuid = $request->header('x-uuid');
            
            $company = Company::where('uuid', $uuid)
                ->select('idioma')
                ->first();

            return response()->json($company);
        } catch (\Exception $e) {
            \Log::error("|CompanyController|getIdioma: '".$e->getMessage()."'");
            return response()->json([
                'msg' => 'error',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}
