<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModulo extends Model
{
    use HasFactory;

    protected $table = 'companies_modulos';

    protected $fillable = [
        'id_empresa',
        'id_modulo',
        'activo',
    ];

    protected $casts = [
        'id_empresa' => 'integer',
        'id_modulo' => 'integer',
        'activo' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_empresa');
    }
}
