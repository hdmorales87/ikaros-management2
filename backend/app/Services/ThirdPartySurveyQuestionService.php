<?php

namespace App\Services;

use App\Models\Company;

class ThirdPartySurveyQuestionService
{
    public function list(string $uuid, string $type): array
    {
        return $this->connection($uuid)->table('encuesta_terceros_preguntas')
            ->select(['id', 'tipo', 'nombre'])
            ->where('tipo', $this->type($type))
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($question) => (array) $question)
            ->all();
    }

    public function create(string $uuid, array $data): array
    {
        $connection = $this->connection($uuid);
        $questionId = $connection->table('encuesta_terceros_preguntas')->insertGetId([
            'tipo' => $this->type($data['tipo']),
            'nombre' => $data['nombre'],
            'activo' => 1,
        ]);

        return (array) $connection->table('encuesta_terceros_preguntas')
            ->select(['id', 'tipo', 'nombre'])
            ->where('id', $questionId)
            ->first();
    }

    public function update(string $uuid, int $questionId, array $data): ?array
    {
        $connection = $this->connection($uuid);
        $updated = $connection->table('encuesta_terceros_preguntas')
            ->where('id', $questionId)
            ->where('activo', 1)
            ->update(['tipo' => $this->type($data['tipo']), 'nombre' => $data['nombre']]);
        if (!$updated) {
            return null;
        }

        return (array) $connection->table('encuesta_terceros_preguntas')
            ->select(['id', 'tipo', 'nombre'])
            ->where('id', $questionId)
            ->first();
    }

    public function deactivate(string $uuid, int $questionId): bool
    {
        return $this->connection($uuid)->table('encuesta_terceros_preguntas')
            ->where('id', $questionId)
            ->where('activo', 1)
            ->update(['activo' => 0]) > 0;
    }

    private function type(string $type): string
    {
        abort_unless(in_array($type, ['cliente', 'proveedor'], true), 422, 'El tipo de pregunta no es válido.');
        return $type;
    }

    private function connection(string $uuid)
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
