<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class NotificationService
{
    public function __construct(private readonly MailService $mailService)
    {
    }

    public function risk(int $riskId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        $risk = $connection->table('proyectos_riesgos as risks')
            ->join('proyectos as projects', 'projects.id', '=', 'risks.id_proyecto')
            ->join('users as responsible', 'responsible.id', '=', 'risks.id_responsable')
            ->join('users as creator', 'creator.id', '=', 'risks.id_usuario')
            ->join('proyectos_actividades as activities', 'activities.id', '=', 'risks.id_actividad')
            ->leftJoin('proyectos_subactividades as subactivities', 'subactivities.id', '=', 'risks.id_subactividad')
            ->where('risks.id', $riskId)
            ->first(['risks.nombre', 'risks.descripcion', 'risks.mitigacion', 'projects.codigo', 'projects.nombre as proyecto', 'activities.nombre as actividad', 'subactivities.nombre as subactividad', 'responsible.email as email_responsable', 'creator.email as email_creador']);
        if (!$risk) return ['msg' => 'not_found'];
        $content = '<h1>Riesgo de proyecto asignado</h1><p><strong>Proyecto:</strong> '.e($risk->codigo).' - '.e($risk->proyecto).'</p><p><strong>Actividad:</strong> '.e($risk->actividad).'</p><p><strong>Riesgo:</strong> '.e($risk->nombre).'</p><p><strong>Descripción:</strong> '.e($risk->descripcion).'</p><p><strong>Mitigación:</strong> '.e($risk->mitigacion).'</p>';
        $this->mailService->sendMany([$risk->email_responsable, $risk->email_creador], 'Asignación de riesgo de proyecto', $content, $uuid);
        return ['msg' => 'success'];
    }

    public function activity(int $id, string $type, int $userId, string $uuid): array
    {
        abort_unless(in_array($type, ['principal', 'adicional'], true), 422, 'Tipo de responsable inválido.');
        $connection = $this->connection($uuid);
        $query = $type === 'principal'
            ? $connection->table('proyectos_actividades as activities')->join('proyectos as projects', 'projects.id', '=', 'activities.id_proyecto')->join('users as responsible', 'responsible.id', '=', 'activities.id_responsable')->where('activities.id', $id)->first(['activities.nombre', 'activities.descripcion', 'activities.fecha_inicio', 'activities.fecha_final', 'projects.codigo', 'projects.nombre as proyecto', 'responsible.email as email_responsable'])
            : $connection->table('proyectos_actividades_responsables as extra')->join('proyectos_actividades as activities', 'activities.id', '=', 'extra.id_actividad')->join('proyectos as projects', 'projects.id', '=', 'activities.id_proyecto')->join('users as responsible', 'responsible.id', '=', 'extra.id_user')->join('users as owner', 'owner.id', '=', 'activities.id_responsable')->where('extra.id', $id)->first(['activities.nombre', 'activities.descripcion', 'activities.fecha_inicio', 'activities.fecha_final', 'projects.codigo', 'projects.nombre as proyecto', 'responsible.email as email_responsable', 'owner.email as email_principal']);
        if (!$query) return ['msg' => 'not_found'];
        $creator = $connection->table('users')->where('id', $userId)->value('email');
        $content = '<h1>Responsable asignado a actividad</h1><p><strong>Proyecto:</strong> '.e($query->codigo).' - '.e($query->proyecto).'</p><p><strong>Actividad:</strong> '.e($query->nombre).'</p><p><strong>Descripción:</strong> '.e($query->descripcion).'</p><p><strong>Inicio:</strong> '.e($query->fecha_inicio).'</p><p><strong>Fin:</strong> '.e($query->fecha_final).'</p>';
        $this->mailService->sendMany([$query->email_responsable, $query->email_principal ?? null, $creator], 'Asignación de responsable a actividad', $content, $uuid);
        return ['msg' => 'success'];
    }

    public function tableChanges(array $data, int $userId, string $uuid): array
    {
        abort_unless(in_array($data['tabla'], ['proyectos', 'iniciativas'], true), 422, 'Tabla de notificación inválida.');
        $connection = $this->connection($uuid);
        $log = $connection->table('log')->where('tabla', $data['tabla'])->where('id_tabla', $data['id'])->where('seccion_tabla', $data['seccion_tabla'])->where('activo', 1)->orderByDesc('id')->first(['datos', 'accion', 'id_usuario']);
        if (!$log) return ['msg' => 'not_found'];
        $emails = $connection->table('users')->whereIn('id', array_unique([$userId, (int) $log->id_usuario]))->pluck('email')->all();
        if ($data['tabla'] === 'proyectos') {
            $emails = array_merge($emails, $connection->table('proyectos')->join('users', 'users.id', '=', 'proyectos.id_responsable')->where('proyectos.id', $data['id'])->pluck('users.email')->all());
        } else {
            $emails = array_merge($emails, $connection->table('iniciativas')->join('users', 'users.id', '=', 'iniciativas.id_propietario')->where('iniciativas.id', $data['id'])->pluck('users.email')->all());
        }
        $this->mailService->sendMany($emails, 'Notificación de cambios en '.$data['titulo'], '<h1>Cambios en '.e($data['titulo']).'</h1><p><strong>Acción:</strong> '.e($log->accion).'</p><p>'.nl2br(e((string) $log->datos)).'</p>', $uuid);
        return ['msg' => 'success'];
    }

    public function committee(int $committeeId, int $userId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        $emails = $connection->table('iniciativas_comites_aprobadores as approvers')->join('users', 'users.id', '=', 'approvers.id_user')->where('approvers.id_comite', $committeeId)->where('approvers.activo', 1)->where('approvers.estado_validacion', '<>', 2)->pluck('users.email')->all();
        $emails[] = $connection->table('users')->where('id', $userId)->value('email');
        $this->mailService->sendMany($emails, 'Solicitud de aprobación de comité', '<h1>Solicitud de aprobación</h1><p>Hay una iniciativa pendiente de revisión en el comité.</p>', $uuid);
        return ['msg' => 'success'];
    }

    public function initiative(int $initiativeId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        $emails = $connection->table('iniciativas')->join('users', 'users.id', '=', 'iniciativas.id_propietario')->where('iniciativas.id', $initiativeId)->pluck('users.email')->all();
        $emails = array_merge($emails, $connection->table('iniciativas_comites_aprobadores as approvers')->join('users', 'users.id', '=', 'approvers.id_user')->where('approvers.id_iniciativa', $initiativeId)->where('approvers.activo', 1)->pluck('users.email')->all());
        $this->mailService->sendMany($emails, 'Validación de iniciativa', '<h1>Validación de iniciativa</h1><p>Hay una iniciativa pendiente de validación.</p>', $uuid);
        return ['msg' => 'success'];
    }

    public function training(int $trainingId, string $uuid): array
    {
        $connection = $this->connection($uuid);
        $training = $connection->table('capacitaciones')->where('id', $trainingId)->first(['nombre', 'instructor', 'lugar', 'observaciones', 'fecha_inicio', 'fecha_final']);
        if (!$training) return ['msg' => 'not_found'];
        $emails = $connection->table('capacitaciones_usuarios as attendees')->join('users', 'users.id', '=', 'attendees.id_usuario')->where('attendees.id_capacitacion', $trainingId)->pluck('users.email')->all();
        $content = '<h1>Nueva capacitación</h1><p><strong>Nombre:</strong> '.e($training->nombre).'</p><p><strong>Instructor:</strong> '.e($training->instructor).'</p><p><strong>Lugar:</strong> '.e($training->lugar).'</p><p><strong>Inicio:</strong> '.e($training->fecha_inicio).'</p><p><strong>Fin:</strong> '.e($training->fecha_final).'</p><p>'.e($training->observaciones).'</p>';
        $this->mailService->sendMany($emails, 'Nueva capacitación', $content, $uuid);
        return ['msg' => 'success'];
    }

    public function hours(int $id, string $type, string $uuid): array
    {
        abort_unless(in_array($type, ['planeadas', 'ejecutadas'], true), 422, 'Tipo de horas inválido.');
        $connection = $this->connection($uuid);
        $record = $connection->table('proyectos_horas_registro')->where('id', $id)->first(['id_colaborador', 'id_usuario_validacion_'.$type, 'horas_'.$type, 'observaciones_'.$type]);
        if (!$record) return ['msg' => 'not_found'];
        $emails = $connection->table('users')->whereIn('id', [$record->{'id_colaborador'}, $record->{'id_usuario_validacion_'.$type}])->pluck('email')->all();
        $this->mailService->sendMany($emails, 'Notificación de horas '.$type, '<h1>Actualización de horas</h1><p><strong>Horas:</strong> '.e($record->{'horas_'.$type}).'</p><p>'.e($record->{'observaciones_'.$type}).'</p>', $uuid);
        return ['msg' => 'success'];
    }

    public function confirmedHours(array $ids, string $type, int $userId, string $uuid): array
    {
        abort_unless(in_array($type, ['planeadas', 'ejecutadas'], true), 422, 'Tipo de horas inválido.');
        $connection = $this->connection($uuid);
        $records = $connection->table('proyectos_horas_registro')->whereIn('id', $ids)->get(['id', 'id_colaborador', 'horas_'.$type, 'observaciones_'.$type]);
        if ($records->isEmpty()) return ['msg' => 'not_found'];
        $connection->transaction(function () use ($connection, $records, $type, $userId): void {
            $connection->table('proyectos_horas_registro')->whereIn('id', $records->pluck('id'))->update([
                'estado_validacion_'.$type => 1,
                'id_usuario_validacion_'.$type => $userId,
                'fecha_validacion_'.$type => now()->toDateString(),
            ]);
            $connection->table('proyectos_horas_registro_seguimientos')->insert($records->map(fn ($record) => [
                'id_registro' => $record->id,
                'suceso' => 'Horas '.$type.' confirmadas',
                'descripcion' => 'Confirmación registrada desde el módulo de horas.',
                'id_usuario' => $userId,
                'fecha' => now(),
                'activo' => 1,
            ])->all());
        });
        $emails = $connection->table('users')->whereIn('id', $records->pluck('id_colaborador'))->pluck('email')->all();
        $summary = $records->map(fn ($record) => 'Horas: '.$record->{'horas_'.$type}.' - '.$record->{'observaciones_'.$type})->implode('<br>');
        $this->mailService->sendMany($emails, 'Confirmación de horas '.$type, '<h1>Horas confirmadas</h1><p>'.$summary.'</p>', $uuid);
        return ['msg' => 'success'];
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
