<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class CatalogService
{
    private const CATALOGS = [
        'service-areas' => ['table' => 'areas_servicio', 'fields' => ['nombre', 'capacidad_atencion'], 'rules' => ['nombre' => ['required', 'string', 'max:255'], 'capacidad_atencion' => ['required', 'integer', 'min:1']]],
        'departments' => ['table' => 'departamentos', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'service-categories' => ['table' => 'areas_servicio_categorias', 'fields' => ['id_area', 'nombre'], 'rules' => ['id_area' => ['required', 'integer', 'min:1'], 'nombre' => ['required', 'string', 'max:255']]],
        'service-subcategories' => ['table' => 'areas_servicio_subcategorias', 'fields' => ['id_categoria', 'nombre'], 'rules' => ['id_categoria' => ['required', 'integer', 'min:1'], 'nombre' => ['required', 'string', 'max:255']]],
        'asset-types' => ['table' => 'activos_tipos', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'currencies' => ['table' => 'monedas', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'documentation-types' => ['table' => 'tipos_documentacion', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'file-extensions' => ['table' => 'extensiones', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:100']]],
        'satisfaction-questions' => ['table' => 'encuesta_satisfaccion_preguntas', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:1000']]],
        'contract-states' => ['table' => 'terceros_contratos_estados', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'payment-plans' => ['table' => 'terceros_contratos_planes_pagos', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'holidays' => ['table' => 'dias_festivos', 'fields' => ['fecha', 'descripcion'], 'rules' => ['fecha' => ['required', 'date'], 'descripcion' => ['required', 'string', 'max:1000']]],
        'risk-probabilities' => ['table' => 'proyectos_riesgos_probabilidad', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'risk-impacts' => ['table' => 'proyectos_riesgos_impacto', 'fields' => ['nombre'], 'rules' => ['nombre' => ['required', 'string', 'max:255']]],
        'imap-accounts' => ['table' => 'imap', 'fields' => ['servidor', 'correo', 'puerto', 'tls'], 'rules' => ['servidor' => ['required', 'string', 'max:255'], 'correo' => ['required', 'email', 'max:255'], 'password' => ['required', 'string', 'max:255'], 'puerto' => ['required', 'integer', 'min:1', 'max:65535'], 'tls' => ['required', 'in:true,false']]],
    ];

    public function list(string $catalog, string $uuid): array
    {
        $definition = $this->definition($catalog);
        return $this->connection($uuid)->table($definition['table'])
            ->select(['id', ...$definition['fields'], 'activo'])
            ->where('activo', 1)
            ->orderBy($definition['fields'][0])
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function create(string $catalog, array $data, string $uuid): array
    {
        $definition = $this->definition($catalog);
        $connection = $this->connection($uuid);
        $id = $connection->table($definition['table'])->insertGetId([...$data, 'activo' => 1]);
        return (array) $connection->table($definition['table'])->select(['id', ...$definition['fields'], 'activo'])->where('id', $id)->first();
    }

    public function update(string $catalog, int $id, array $data, string $uuid): ?array
    {
        $definition = $this->definition($catalog);
        $connection = $this->connection($uuid);
        if (!$connection->table($definition['table'])->where('id', $id)->where('activo', 1)->exists()) return null;
        $connection->table($definition['table'])->where('id', $id)->update($data);
        return (array) $connection->table($definition['table'])->select(['id', ...$definition['fields'], 'activo'])->where('id', $id)->first();
    }

    public function deactivate(string $catalog, int $id, string $uuid): bool
    {
        $definition = $this->definition($catalog);
        return $this->connection($uuid)->table($definition['table'])->where('id', $id)->where('activo', 1)->update(['activo' => 0]) === 1;
    }

    public function rules(string $catalog, bool $creating): array
    {
        $rules = $this->definition($catalog)['rules'];
        if (!$creating) {
            foreach ($rules as &$fieldRules) $fieldRules[0] = 'sometimes';
        }
        return $rules;
    }

    private function definition(string $catalog): array
    {
        abort_unless(isset(self::CATALOGS[$catalog]), 404, 'Catálogo no encontrado.');
        return self::CATALOGS[$catalog];
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
