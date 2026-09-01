<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;

class TrainingService
{
    private const FIELDS = ['nombre', 'instructor', 'intensidad', 'fecha_inicio', 'hora_inicio', 'fecha_final', 'hora_final', 'lugar', 'observaciones'];

    public function list(string $uuid): array
    {
        return $this->connection($uuid)->table('capacitaciones')->select(['id', ...self::FIELDS])->where('activo', 1)->orderByDesc('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function find(string $uuid, int $trainingId): ?array
    {
        $training = $this->connection($uuid)->table('capacitaciones')->select(['id', ...self::FIELDS])->where('id', $trainingId)->where('activo', 1)->first();
        return $training ? (array) $training : null;
    }

    public function create(string $uuid, array $data): array
    {
        $this->validateDates($data);
        $connection = $this->connection($uuid);
        $trainingId = $connection->table('capacitaciones')->insertGetId([...$data, 'activo' => 1]);
        return $this->find($uuid, $trainingId);
    }

    public function update(string $uuid, int $trainingId, array $data): ?array
    {
        $current = $this->find($uuid, $trainingId);
        if (!$current) return null;
        $this->validateDates([...$current, ...$data]);
        $this->connection($uuid)->table('capacitaciones')->where('id', $trainingId)->update($data);
        return $this->find($uuid, $trainingId);
    }

    public function deactivate(string $uuid, int $trainingId): bool
    {
        return $this->connection($uuid)->table('capacitaciones')->where('id', $trainingId)->where('activo', 1)->update(['activo' => 0]) > 0;
    }

    public function attendees(string $uuid, int $trainingId): ?array
    {
        $connection = $this->connection($uuid);
        if (!$this->activeTraining($connection, $trainingId)) return null;
        return $connection->table('capacitaciones_usuarios')->select(['id', 'id_usuario', 'asistencia'])->where('id_capacitacion', $trainingId)->where('activo', 1)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function addAttendee(string $uuid, int $trainingId, int $userId): ?array
    {
        $connection = $this->connection($uuid);
        if (!$this->activeTraining($connection, $trainingId)) return null;
        abort_unless($connection->table('users')->where('id', $userId)->where('activo', 1)->exists(), 422, 'El usuario no existe o está inactivo.');
        abort_if($connection->table('capacitaciones_usuarios')->where('id_capacitacion', $trainingId)->where('id_usuario', $userId)->where('activo', 1)->exists(), 422, 'El usuario ya está inscrito.');
        $id = $connection->table('capacitaciones_usuarios')->insertGetId(['id_capacitacion' => $trainingId, 'id_usuario' => $userId, 'asistencia' => 'false', 'activo' => 1]);
        return (array) $connection->table('capacitaciones_usuarios')->select(['id', 'id_usuario', 'asistencia'])->where('id', $id)->first();
    }

    public function updateAttendance(string $uuid, int $trainingId, int $attendeeId, bool $attended): ?array
    {
        $connection = $this->connection($uuid);
        if (!$this->activeTraining($connection, $trainingId)) return null;
        $updated = $connection->table('capacitaciones_usuarios')->where('id', $attendeeId)->where('id_capacitacion', $trainingId)->where('activo', 1)->update(['asistencia' => $attended ? 'true' : 'false']);
        return $updated ? (array) $connection->table('capacitaciones_usuarios')->select(['id', 'id_usuario', 'asistencia'])->where('id', $attendeeId)->first() : null;
    }

    public function removeAttendee(string $uuid, int $trainingId, int $attendeeId): bool
    {
        return $this->connection($uuid)->table('capacitaciones_usuarios')->where('id', $attendeeId)->where('id_capacitacion', $trainingId)->where('activo', 1)->update(['activo' => 0]) > 0;
    }

    private function validateDates(array $data): void
    {
        if (!empty($data['fecha_inicio']) && !empty($data['fecha_final'])) abort_if($data['fecha_final'] < $data['fecha_inicio'] || ($data['fecha_final'] === $data['fecha_inicio'] && !empty($data['hora_inicio']) && !empty($data['hora_final']) && $data['hora_final'] < $data['hora_inicio']), 422, 'La fecha y hora final deben ser posteriores a las iniciales.');
    }

    private function activeTraining(Connection $connection, int $trainingId): bool
    {
        return $connection->table('capacitaciones')->where('id', $trainingId)->where('activo', 1)->exists();
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
