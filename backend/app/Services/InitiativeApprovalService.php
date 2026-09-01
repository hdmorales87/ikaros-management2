<?php

namespace App\Services;

use App\Models\Company;

class InitiativeApprovalService
{
    public function trace(string $uuid): array
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid)
            ->table('iniciativas_comites_aprobadores as approver')
            ->select([
                'approver.id',
                'committee.nombre as comite',
                'user.nombre as nombre_usuario',
                'user.apellido as apellido_usuario',
                'approver.estado_validacion',
            ])
            ->join('iniciativas_comites as committee', 'committee.id', '=', 'approver.id_comite')
            ->join('users as user', 'user.id', '=', 'approver.id_user')
            ->where('approver.activo', 1)
            ->where('committee.activo', 1)
            ->where('user.activo', 1)
            ->orderBy('committee.nombre')
            ->orderBy('user.nombre')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }
}
