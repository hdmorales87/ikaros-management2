<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyModulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    public function sincronizarEmpresa(object $userData): array
    {
        try {
            $company = Company::where('documento', $userData->documento)->first();

            if ($company) {
                $company->update([
                    'tipo_licencia' => $userData->data['tipo_licencia'],
                    'fecha_vencimiento_licencia' => $userData->data['fecha_vencimiento_licencia'],
                    'maximo_usuarios' => $userData->data['maximo_usuarios'],
                    'cuota_almacenamiento' => $userData->data['cuota_almacenamiento'],
                ]);
                return ['msg' => 'success'];
            }

            return ['msg' => 'not_exists'];
        } catch (\Exception $e) {
            Log::error("|CompanyService|sincronizarEmpresa: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function guardaModulos(object $userData): array
    {
        try {
            $arrayModulos = $userData->arrayModulos;
            $idRow = $userData->idRow;

            CompanyModulo::where('id_empresa', $idRow)->delete();

            foreach ($arrayModulos as $modulo) {
                CompanyModulo::create([
                    'id_empresa' => $idRow,
                    'id_modulo' => $modulo,
                ]);
            }

            return ['msg' => 'success'];
        } catch (\Exception $e) {
            Log::error("|CompanyService|guardaModulos: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function crearDirectorios(object $userData): array
    {
        try {
            $company = Company::where('id', $userData->idEmpresa)->first(['uuid']);

            if ($company) {
                $path = public_path('storage/' . $company->uuid);
                
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
            }

            return ['msg' => 'success'];
        } catch (\Exception $e) {
            Log::error("|CompanyService|crearDirectorios: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }
}
