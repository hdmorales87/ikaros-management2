<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users', 'id_rol', 'id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'roles_permisos', 'id_rol', 'id_permiso');
    }

    public function getPermisosByRole(int $roleId, string $uuid): array
    {
        $company = new Company();
        $connection = $company->getConnectionByUUID($uuid);
        
        return $connection->table('roles_permisos AS RP')
            ->select('RP.id_permiso')
            ->join('roles AS R', 'R.id', '=', 'RP.id_rol')
            ->where('RP.id_rol', $roleId)
            ->get()
            ->toArray();
    }
}
