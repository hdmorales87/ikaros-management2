<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class TerceroService
{
    public function paginateClients(string $uuid, string $search, int $perPage, string $sort): array
    {
        return $this->paginate($uuid, 'cliente', $search, $perPage, $sort);
    }

    public function paginateProviders(string $uuid, string $search, int $perPage, string $sort): array
    {
        return $this->paginate($uuid, 'proveedor', $search, $perPage, $sort);
    }

    public function survey(int $lastId, int $thirdPartyId, string $uuid): array
    {
        return $this->connection($uuid)->table('encuesta_terceros')
            ->select('id')->where('id', '>', $lastId)->where('id_tercero', $thirdPartyId)->where('tipo', 'cliente')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function find(int $id, string $uuid): ?array
    {
        $row = $this->connection($uuid)->table('terceros')->where('id', $id)->first();
        return $row ? (array) $row : null;
    }

    public function questions(string $uuid): array
    {
        return $this->connection($uuid)->table('encuesta_terceros_preguntas')->where('tipo', 'cliente')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function saveSurvey(int $thirdPartyId, int $userId, string $type, array $answers, string $uuid): int
    {
        abort_unless(in_array($type, ['cliente', 'proveedor'], true), 422, 'Tipo de tercero inválido.');
        $connection = $this->connection($uuid);
        return $connection->transaction(function () use ($connection, $thirdPartyId, $userId, $type, $answers): int {
            $surveyId = $connection->table('encuesta_terceros')->insertGetId(['id_tercero' => $thirdPartyId, 'id_usuario' => $userId, 'fecha' => now(), 'tipo' => $type]);
            $score = 0;
            $count = 0;
            $rows = [];
            foreach ($answers as $questionId => $answer) {
                $questionId = (int) $questionId;
                if ($questionId === 0) {
                    $rows[] = ['id_encuesta' => $surveyId, 'nombre_pregunta' => null, 'id_pregunta' => 0, 'puntaje' => 0, 'comentarios' => (string) $answer];
                    continue;
                }
                abort_unless(is_numeric($answer) && (int) $answer >= 1 && (int) $answer <= 5, 422, 'Puntaje de encuesta inválido.');
                $name = $connection->table('encuesta_terceros_preguntas')->where('id', $questionId)->value('nombre');
                abort_unless($name !== null, 422, 'Pregunta de encuesta inválida.');
                $score += (int) $answer;
                $count++;
                $rows[] = ['id_encuesta' => $surveyId, 'nombre_pregunta' => $name, 'id_pregunta' => $questionId, 'puntaje' => (int) $answer, 'comentarios' => ''];
            }
            abort_unless($count > 0, 422, 'La encuesta no tiene respuestas.');
            $connection->table('encuesta_terceros_respuestas')->insert($rows);
            $average = $score / $count;
            $connection->table('encuesta_terceros')->where('id', $surveyId)->update(['promedio' => $average]);
            $connection->table('terceros')->where('id', $thirdPartyId)->update(['puntaje_'.$type => $connection->table('encuesta_terceros')->where('id_tercero', $thirdPartyId)->where('tipo', $type)->avg('promedio')]);
            return (int) $surveyId;
        });
    }

    public function invitation(int $thirdPartyId, string $uuid, string $appUrl): array
    {
        $connection = $this->connection($uuid);
        $thirdParty = $connection->table('terceros')->where('id', $thirdPartyId)->first(['email', 'nombre_contacto', 'razon_social']);
        if (!$thirdParty || !filter_var($thirdParty->email ?? null, FILTER_VALIDATE_EMAIL)) return ['msg' => 'not_found'];
        $lastId = (int) ($connection->table('encuesta_terceros')->where('id_tercero', $thirdPartyId)->where('tipo', 'cliente')->max('id') ?? 0);
        $link = rtrim($appUrl, '/').'/encuestaCliente/'.base64_encode((string) $lastId).'/'.base64_encode((string) $thirdPartyId).'/'.base64_encode($uuid);
        return ['email' => $thirdParty->email, 'name' => $thirdParty->nombre_contacto, 'company' => $thirdParty->razon_social, 'link' => $link];
    }

    private function paginate(string $uuid, string $type, string $search, int $perPage, string $sort): array
    {
        $scoreColumn = 'puntaje_'.$type;
        $query = $this->connection($uuid)->table('terceros')
            ->select(['id', 'documento', 'razon_social', 'nombre_comercial', 'email', $scoreColumn])
            ->where('activo', 1)
            ->where($type, 'true');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('documento', 'like', '%'.$search.'%')
                    ->orWhere('razon_social', 'like', '%'.$search.'%')
                    ->orWhere('nombre_comercial', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $this->applySort($query, $sort, $scoreColumn);
        $paginator = $query->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    private function applySort($query, string $sort, string $scoreColumn): void
    {
        $allowed = ['id', 'documento', 'razon_social', 'email', $scoreColumn];
        $applied = false;

        foreach (array_filter(explode(',', $sort)) as $requested) {
            $column = ltrim($requested, '-');
            abort_unless(in_array($column, $allowed, true), 422, 'Campo de ordenamiento no permitido.');
            $query->orderBy($column, str_starts_with($requested, '-') ? 'desc' : 'asc');
            $applied = true;
        }

        if (!$applied) {
            $query->orderBy('razon_social');
        }
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
