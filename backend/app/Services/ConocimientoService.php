<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class ConocimientoService
{
    public function list(string $uuid, ?string $topic = null): array
    {
        $query = $this->connection($uuid)->table('conocimiento')
            ->select(['id', 'tema'])
            ->where('activo', 1)
            ->whereRaw("TRIM(COALESCE(solucion, '')) <> ''")
            ->orderBy('tema');

        if ($topic !== null && $topic !== '') {
            $query->where('tema', 'like', '%'.$topic.'%');
        }

        return $query->get()->map(fn ($item) => (array) $item)->all();
    }

    public function find(int $id, string $uuid): ?array
    {
        $item = $this->connection($uuid)->table('conocimiento')
            ->select(['id', 'tema', 'solucion'])
            ->where('activo', 1)
            ->where('id', $id)
            ->first();

        return $item ? (array) $item : null;
    }

    public function rate(int $knowledgeId, int $userId, int $score, string $uuid): string
    {
        $connection = $this->connection($uuid);
        $exists = $connection->table('conocimiento_reputacion')
            ->where('id_usuario', $userId)
            ->where('id_conocimiento', $knowledgeId)
            ->exists();

        if ($exists) {
            return 'no_change';
        }

        $connection->transaction(function () use ($connection, $knowledgeId, $userId, $score): void {
            $connection->table('conocimiento_reputacion')->insert([
                'id_usuario' => $userId,
                'id_conocimiento' => $knowledgeId,
                'puntaje' => $score,
            ]);

            $average = $connection->table('conocimiento_reputacion')
                ->where('id_conocimiento', $knowledgeId)
                ->avg('puntaje');

            $connection->table('conocimiento')
                ->where('id', $knowledgeId)
                ->update(['reputacion' => round((float) $average)]);
        });

        return 'success';
    }

    private function connection(string $uuid): Connection
    {
        if ($uuid === '') {
            throw new \InvalidArgumentException('El identificador de empresa es obligatorio.');
        }

        return (new Company())->getConnectionByUUID($uuid);
    }
}
