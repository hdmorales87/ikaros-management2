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

    public function chatbotMenu(string $uuid): array
    {
        $company = Company::where('uuid', $uuid)->first(['id']);
        if (!$company) return [];
        return DB::connection('global')->table('companies_chatbot_options as options')
            ->select('master.id as opcion', 'master.descripcion', DB::raw('LOWER(modules.nombre) as modulo'), 'master.orden')
            ->join('chatbot_options_maestro as master', 'master.id', '=', 'options.id_opcion')
            ->join('companies_modulos as company_modules', function ($join): void {
                $join->on('company_modules.id_empresa', '=', 'options.id_empresa')->on('company_modules.id_modulo', '=', 'master.id_modulo');
            })
            ->join('modulos as modules', 'modules.id', '=', 'master.id_modulo')
            ->where('options.id_empresa', $company->id)->where('options.activo', 1)->where('company_modules.activo', 1)
            ->orderBy('master.orden')->get()->map(fn ($row) => (array) $row)->all();
    }
}
