<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'documento',
        'razon_social',
        'tipo_licencia',
        'fecha_vencimiento_licencia',
        'maximo_usuarios',
        'cuota_almacenamiento',
        'uuid',
        'bd',
        'bd_ubicacion',
        'idioma',
        'id_local',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento_licencia' => 'date',
        'maximo_usuarios' => 'integer',
        'cuota_almacenamiento' => 'integer',
        'activo' => 'boolean',
        'id_local' => 'integer',
    ];

    public function modulos(): HasMany
    {
        return $this->hasMany(CompanyModulo::class, 'id_empresa');
    }

    public function getConnectionByUUID(string $uuid): \Illuminate\Database\Connection
    {
        // UUID especial para la base de datos global
        if ($uuid === 'ad2a15dcc11e41f68e6eea89a990a908') {
            return \DB::connection('global');
        }

        // Buscar la empresa en la base de datos global para obtener el nombre de su BD
        $company = self::where('uuid', $uuid)->first(['bd']);
        
        if (!$company) {
            throw new \Exception("Company not found for UUID: {$uuid}");
        }

        // Conectar a la base de datos específica del tenant
        return \DB::connection($company->bd);
    }

    public function getCompanyByUUID(string $uuid): ?self
    {
        return self::where('uuid', $uuid)
            ->select([
                'id',
                'documento',
                'razon_social',
                'id_local',
                'tipo_licencia',
                'fecha_vencimiento_licencia',
                \DB::raw('DATEDIFF(fecha_vencimiento_licencia, CURDATE()) AS dias_vencimiento'),
                'maximo_usuarios',
                'cuota_almacenamiento',
                'uuid',
                'bd',
            ])
            ->first();
    }
}
