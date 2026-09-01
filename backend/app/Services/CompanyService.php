<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyModulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    public function checkCompany(string $documento): ?array
    {
        $instalacion = (string) config('app.instalacion');
        $cloudUrl = (string) config('services.ikaros_cloud.url');
        $applicationId = (string) config('services.ikaros_cloud.application_id');
        $applicationKey = (string) config('services.ikaros_cloud.application_key');

        Log::debug('|CompanyService|checkCompany: variables de configuración resueltas', [
            'instalacion' => $instalacion,
            'cloud_url' => $cloudUrl,
            'application_id' => $this->mask($applicationId),
            'application_key' => $this->mask($applicationKey),
        ]);

        // The cloud install and any deployment without a configured registry resolve locally.
        if ($instalacion === 'cloud' || $cloudUrl === '') {
            return $this->findLocalCompany($documento);
        }

        return $this->checkCompanyAgainstCloud($documento, $instalacion, $cloudUrl);
    }

    // Keeps credentials out of the log while still proving they resolved to something.
    private function mask(string $value): string
    {
        if ($value === '') {
            return '(vacío)';
        }

        return substr($value, 0, 4).'...'.substr($value, -4);
    }

    private function checkCompanyAgainstCloud(string $documento, string $instalacion, string $cloudUrl): ?array
    {
        Log::info("|CompanyService|checkCompany: consultando registro en la nube para documento '{$documento}'");

        $response = Http::timeout(8)
            ->withHeaders(array_filter([
                'X-Application-Id' => config('services.ikaros_cloud.application_id'),
                'X-Application-Key' => config('services.ikaros_cloud.application_key'),
            ]))
            ->post(rtrim($cloudUrl, '/').'/checkCompany', [
                'company' => $documento,
                'instalation' => $instalacion,
            ])
            ->throw();

        $remote = $response->json();
        if (empty($remote['uuid'])) {
            Log::info("|CompanyService|checkCompany: la nube no registra el documento '{$documento}'");
            return null;
        }

        // Sync identity only; license fields stay owned by sincronizarEmpresa so a local install can't self-extend them.
        Company::updateOrCreate(
            ['documento' => $documento],
            ['uuid' => $remote['uuid'], 'razon_social' => $remote['razon_social'] ?? null, 'bd_ubicacion' => $instalacion, 'activo' => 1],
        );

        return $this->findLocalCompany($documento);
    }

    private function findLocalCompany(string $documento): ?array
    {
        $company = Company::where('documento', $documento)
            ->where('bd_ubicacion', config('app.instalacion'))
            ->where('activo', 1)
            ->first(['uuid', 'razon_social']);

        return $company ? $company->only(['uuid', 'razon_social']) : null;
    }

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
