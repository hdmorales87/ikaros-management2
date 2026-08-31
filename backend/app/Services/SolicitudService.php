<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SolicitudService
{
    public function __construct(private readonly MailService $mailService)
    {
    }

    private const MODULE_TABLES = [
        'incidencias' => 'incidencias',
        'problemas' => 'incidencias',
        'servicios' => 'servicios',
    ];

    public function reject(string $table, int $id, string $observation, int $userId, string $uuid): void
    {
        abort_unless(isset(self::MODULE_TABLES[$table]), 422, 'Tabla de solicitud inválida.');
        $connection = $this->connection($uuid);
        $connection->transaction(function () use ($connection, $table, $id, $observation, $userId): void {
            $connection->table(self::MODULE_TABLES[$table])->where('id', $id)->update(['estado' => 4]);
            $connection->table($table.'_seguimientos')->insert(['id_maestro' => $id, 'estado' => 'Rechazado', 'observacion' => $observation, 'id_usuario' => $userId, 'fecha' => now()]);
        });
    }

    public function recategorize(int $id, int $area, int $category, int $subcategory, string $observation, int $userId, string $uuid): void
    {
        $connection = $this->connection($uuid);
        $connection->transaction(function () use ($connection, $id, $area, $category, $subcategory, $observation, $userId): void {
            $connection->table('incidencias')->where('id', $id)->update(['id_area' => $area, 'id_categoria' => $category, 'id_subcategoria' => $subcategory, 'problema' => 1, 'fecha_problema' => now(), 'estado' => 0]);
            $connection->table('incidencias_seguimientos')->insert(['id_maestro' => $id, 'estado' => 'Recategorizada a Problema', 'observacion' => $observation, 'id_usuario' => $userId, 'fecha' => now()]);
        });
    }

    public function manage(array $data, int $userId, string $uuid): array
    {
        $table = $this->moduleTable((string) $data['tabla']);
        $option = in_array($data['opcion'], ['incidencia', 'problema', 'servicio'], true) ? $data['opcion'] : abort(422, 'Tipo de gestión inválido.');
        $connection = $this->connection($uuid);
        $updates = ['estado' => (int) $data['estado']];
        if (isset($data['idAsignado'])) $updates['id_tecnico_'.$option] = (int) $data['idAsignado'];
        if ((int) $data['estado'] === 1) $updates['fecha_asignacion_'.$option] = now();
        if ((int) $data['estado'] === 3) $updates['fecha_solucion_'.$option] = now();
        if ($option === 'problema' && isset($data['causaProblema'])) $updates['causa'] = $data['causaProblema'];
        if ((int) $data['estado'] === 2 && (int) ($data['estadoActual'] ?? 0) === 1) {
            $priority = (int) $connection->table($table)->where('id', $data['idRow'])->value('prioridad');
            $sla = $this->calculateSla($connection, (int) $data['idRow'], $table, $option, $priority);
            if ($sla === null) return ['msg' => 'no_sla'];
            $updates['fecha_proceso_'.$option] = $sla['started_at'];
            $updates['fecha_vencimiento_'.$option] = $sla['due_at'];
        }
        $connection->transaction(function () use ($connection, $table, $data, $updates, $userId): void {
            $connection->table($table)->where('id', $data['idRow'])->update($updates);
            $connection->table($table.'_seguimientos')->insert(['id_maestro' => $data['idRow'], 'estado' => $data['name_estado'], 'observacion' => $data['observacion'], 'id_usuario' => $userId, 'fecha' => now()]);
        });
        return ['msg' => 'success'];
    }

    private function calculateSla(Connection $connection, int $id, string $table, string $option, int $priority): ?array
    {
        $request = $connection->table($table)->where('id', $id)->first(['id_area']);
        if (!$request) return null;
        $limitColumn = $option.'_prioridad_'.max(1, min(5, $priority));
        $sla = $connection->table('areas_sla')->where('activo', 1)->where('id_area', $request->id_area)->first();
        if (!$sla || (int) ($sla->{$limitColumn} ?? 0) <= 0) return null;
        $holidays = $connection->table('dias_festivos')->pluck('fecha')->map(fn ($date) => Carbon::parse($date)->toDateString())->all();
        $startedAt = Carbon::now();
        $current = $startedAt->copy();
        $minutes = (int) $sla->{$limitColumn} * 60;
        $count = 0;
        while ($count < $minutes && $count < 1_000_000) {
            $current->addMinute();
            $weekday = ['monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miercoles', 'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sabado', 'sunday' => 'domingo'][strtolower($current->format('l'))];
            $dayAllowed = (string) ($sla->{$weekday} ?? 'true') === 'true' || (int) ($sla->{$weekday} ?? 0) === 1;
            $fullDay = (string) ($sla->tiempo_completo ?? 'true') === 'true';
            $inHours = $fullDay || ($current->format('H:i') >= ($sla->hora_inicio_operativa ?? '00:00') && $current->format('H:i') <= ($sla->hora_fin_operativa ?? '23:59'));
            if ($dayAllowed && !in_array($current->toDateString(), $holidays, true) && $inHours) $count++;
        }
        return $count >= $minutes ? ['started_at' => $startedAt, 'due_at' => $current] : null;
    }

    public function assign(int $id, string $type, string $uuid): array
    {
        $map = ['incidencia' => ['table' => 'incidencias', 'permission' => 37, 'column' => 'incidencia'], 'problema' => ['table' => 'incidencias', 'permission' => 38, 'column' => 'problema'], 'servicio' => ['table' => 'servicios', 'permission' => 39, 'column' => 'servicio']];
        abort_unless(isset($map[$type]), 422, 'Tipo de solicitud inválido.');
        $config = $map[$type];
        $connection = $this->connection($uuid);
        $request = $connection->table($config['table'])->where('id', $id)->first();
        if (!$request) return ['msg' => 'not_found', 'consec' => $id];
        $priority = (int) round(((int) $request->urgencia + (int) $request->impacto) / 2);
        $connection->table($config['table'])->where('id', $id)->update(['prioridad' => $priority]);
        $roles = $connection->table('roles_permisos')->where('id_permiso', $config['permission'])->pluck('id_rol');
        if ($roles->isEmpty()) return ['msg' => 'no_roles', 'consec' => $id];
        $capacity = (int) $connection->table('areas_servicio')->where('id', $request->id_area)->value('capacidad_atencion');
        if ($capacity < 1) return ['msg' => 'no_capacidad', 'consec' => $id];
        $candidates = $connection->table('users')->where('activo', 1)->whereIn('id_rol', $roles)->where('id_area', $request->id_area)->where('id', '<>', $request->id_usuario)->get(['id', 'nombre', 'email']);
        $best = null;
        foreach ($candidates as $candidate) {
            $load = $connection->table($config['table'])->where('id_tecnico_'.$config['column'], $candidate->id)->where('estado', '<', 3)->count();
            if ($load < $capacity && ($best === null || $load < $best['load'])) $best = ['id' => $candidate->id, 'name' => $candidate->nombre, 'email' => $candidate->email, 'load' => $load];
        }
        if (!$best) return ['msg' => 'no_capacidad', 'consec' => $id];
        $connection->transaction(function () use ($connection, $config, $id, $best, $request): void {
            $connection->table($config['table'])->where('id', $id)->update(['estado' => 1, 'id_tecnico_'.$config['column'] => $best['id'], 'fecha_asignacion_'.$config['column'] => now()]);
            $connection->table($config['table'].'_seguimientos')->insert(['id_maestro' => $id, 'estado' => 'Asignacion', 'observacion' => 'Asignacion Automatica a '.$best['name'], 'id_usuario' => $request->id_usuario, 'fecha' => now()]);
        });
        $notified = false;
        try {
            $notified = $this->mailService->send($best['email'], 'Nueva solicitud asignada', '<h1>Nueva solicitud asignada</h1><p>Se te ha asignado la solicitud #'.$id.'.</p>', $uuid);
        } catch (\Throwable $exception) {
            report($exception);
        }
        return ['msg' => 'success', 'consec' => $id, 'idAsignado' => $best['id'], 'notified' => $notified];
    }

    private function moduleTable(string $table): string
    {
        abort_unless(isset(self::MODULE_TABLES[$table]), 422, 'Tabla de solicitud inválida.');
        return self::MODULE_TABLES[$table];
    }

    public function status(string $table, int $id, string $uuid): array
    {
        abort_unless(isset(self::MODULE_TABLES[$table]), 422, 'Tabla de solicitud inválida.');
        $row = $this->connection($uuid)->table(self::MODULE_TABLES[$table].' as request')
            ->leftJoin('solicitudes_estado as state', 'state.valor', '=', 'request.estado')
            ->where('request.id', $id)->first(['request.estado', 'state.nombre as nombre_estado']);
        return $row ? [(array) $row] : [];
    }

    public function validationStatus(int $id, string $uuid): array
    {
        return $this->connection($uuid)->table('iniciativas_comites_aprobadores as approver')
            ->join('iniciativas_comites as committee', 'committee.id', '=', 'approver.id_comite')
            ->join('iniciativas as initiative', 'initiative.id', '=', 'approver.id_iniciativa')
            ->where('approver.id', $id)
            ->get(['initiative.id as id_iniciativa', 'approver.estado_validacion', 'committee.estado_validacion as estado_comite', 'initiative.estado as estado_iniciativa'])
            ->map(fn ($row) => (array) $row)->all();
    }

    public function saveValidation(int $id, array $data, string $uuid): void
    {
        $allowed = ['estado_validacion', 'observacion', 'fecha_validacion'];
        $values = array_intersect_key($data, array_flip($allowed));
        abort_unless($values !== [], 422, 'Datos de validación inválidos.');
        $this->connection($uuid)->table('iniciativas_comites_aprobadores')->where('id', $id)->update($values);
    }

    public function saveValidationFollowup(array $data, string $uuid): void
    {
        $allowed = ['id_maestro', 'id_aprobador', 'estado', 'observacion', 'fecha'];
        $values = array_intersect_key($data, array_flip($allowed));
        abort_unless(isset($values['id_maestro'], $values['estado']), 422, 'Datos de seguimiento inválidos.');
        $this->connection($uuid)->table('iniciativas_comites_aprobadores_seguimientos')->insert($values);
    }

    public function surveyStatus(string $table, int $requestId, string $uuid): array
    {
        abort_unless(isset(self::MODULE_TABLES[$table]), 422, 'Tabla de solicitud inválida.');
        $connection = $this->connection($uuid);
        $request = $connection->table(self::MODULE_TABLES[$table])->where('id', $requestId)->first(['estado', 'tipo_finalizacion']);
        if (!$request) return ['msg' => 'not_found'];
        if ((int) $request->estado === 3) {
            $questions = $connection->table('encuesta_satisfaccion_preguntas')->select(['id', 'nombre'])->where('activo', 1)->get()->map(fn ($row) => (array) $row)->all();
            return ['msg' => $questions === [] ? 'no_preguntas' : $questions];
        }
        if ((int) $request->estado === 5) return ['msg' => $request->tipo_finalizacion === 'encuesta' ? 'diligenciada' : 'sistema'];
        return ['msg' => 'inhabilitada'];
    }

    public function saveSurvey(string $table, int $requestId, array $answers, string $uuid): array
    {
        abort_unless(isset(self::MODULE_TABLES[$table]), 422, 'Tabla de solicitud inválida.');
        $connection = $this->connection($uuid);
        $questions = $connection->table('encuesta_satisfaccion_preguntas')->pluck('nombre', 'id')->all();
        $rows = [];
        $total = 0;
        $count = 0;
        foreach ($answers as $questionId => $answer) {
            $questionId = (int) $questionId;
            if ($questionId === 0) {
                $rows[] = ['id_pregunta' => 0, 'nombre_pregunta' => null, 'id_solicitud' => $requestId, 'tabla' => $table, 'puntaje' => 0, 'comentarios' => (string) $answer];
                continue;
            }
            abort_unless(isset($questions[$questionId]) && is_numeric($answer) && (int) $answer >= 1 && (int) $answer <= 5, 422, 'Respuesta de encuesta inválida.');
            $total += (int) $answer;
            $count++;
            $rows[] = ['id_pregunta' => $questionId, 'nombre_pregunta' => $questions[$questionId], 'id_solicitud' => $requestId, 'tabla' => $table, 'puntaje' => (int) $answer, 'comentarios' => ''];
        }
        abort_unless($count > 0, 422, 'La encuesta no tiene respuestas.');
        $average = $total / $count;
        $connection->transaction(function () use ($connection, $table, $requestId, $rows, $average): void {
            $connection->table('encuesta_satisfaccion_respuestas')->insert($rows);
            $connection->table($table)->where('id', $requestId)->update(['estado' => 5, 'fecha_finalizacion' => now(), 'tipo_finalizacion' => 'encuesta', 'puntaje_encuesta' => $average]);
        });
        return ['msg' => 'success'];
    }

    public function urgencyOptions(string $uuid): array
    {
        return $this->options($uuid, 'solicitudes_urgencia');
    }

    public function impactOptions(string $uuid): array
    {
        return $this->options($uuid, 'solicitudes_impacto');
    }

    public function areasByModule(string $uuid, string $module): array
    {
        $connection = $this->connection($uuid);
        $column = 'atencion_'.$this->moduleKey($module);

        return $connection->table('areas_servicio')
            ->select(['id', 'nombre'])
            ->where('activo', 1)
            ->where($column, 'true')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();
    }

    public function categoriesByArea(string $uuid, int $area): array
    {
        return $this->connection($uuid)->table('areas_servicio_categorias')
            ->select(['id', 'nombre'])
            ->where('activo', 1)
            ->where('id_area', $area)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();
    }

    public function subcategoriesByCategory(string $uuid, int $category): array
    {
        return $this->connection($uuid)->table('areas_servicio_subcategorias')
            ->select(['id', 'nombre'])
            ->where('activo', 1)
            ->where('id_categoria', $category)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();
    }

    public function create(array $data, string $uuid, int $userId): int
    {
        $validated = Validator::make($data, [
            'modulo' => ['required', 'string', 'in:incidencias,problemas,servicios'],
            'urgencia' => ['required', 'integer'],
            'impacto' => ['required', 'integer'],
            'area' => ['required', 'integer'],
            'categoria' => ['required', 'integer'],
            'subcategoria' => ['required', 'integer'],
            'asunto' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
        ])->validate();

        $connection = $this->connection($uuid);
        $table = self::MODULE_TABLES[$validated['modulo']];
        $request = [
            'id_usuario' => $userId,
            'fecha' => now(),
            'urgencia' => $validated['urgencia'],
            'impacto' => $validated['impacto'],
            'id_area' => $validated['area'],
            'id_categoria' => $validated['categoria'],
            'id_subcategoria' => $validated['subcategoria'],
            'asunto' => strip_tags($validated['asunto']),
            'descripcion' => strip_tags($validated['descripcion']),
        ];
        if ($validated['modulo'] === 'problemas') {
            $request['problema'] = 'true';
            $request['fecha_problema'] = now();
        }
        $id = $connection->table($table)->insertGetId($request);

        return (int) $id;
    }

    private function options(string $uuid, string $table): array
    {
        return $this->connection($uuid)->table($table)
            ->select(['id', 'nombre'])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();
    }

    private function connection(string $uuid): Connection
    {
        if ($uuid === '') {
            throw new \InvalidArgumentException('El identificador de empresa es obligatorio.');
        }

        return (new Company())->getConnectionByUUID($uuid);
    }

    private function moduleKey(string $module): string
    {
        return match ($module) {
            'incidencias' => 'incidencias',
            'problemas' => 'problemas',
            'servicios' => 'servicios',
            default => throw new \InvalidArgumentException('Módulo de solicitud inválido.'),
        };
    }
}
