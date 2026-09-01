<?php

namespace App\Services;

use App\Models\Company;

class CalendarService
{
    public function events(string $uuid, string $month): array
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        $connection = (new Company())->getConnectionByUUID($uuid);
        [$firstDay, $lastDay] = $this->monthBounds($month);

        $activities = $connection->table('proyectos_actividades')
            ->select(['nombre', 'fecha_inicio', 'fecha_final'])
            ->where('activo', 1)
            ->where(function ($query) use ($firstDay, $lastDay): void {
                $query->whereBetween('fecha_inicio', [$firstDay, $lastDay])
                    ->orWhereBetween('fecha_final', [$firstDay, $lastDay]);
            })
            ->get()
            ->flatMap(fn ($row) => [
                $this->event($row->fecha_inicio, $row->nombre, 'Inicio de actividad', 'blue'),
                $this->event($row->fecha_final, $row->nombre, 'Fin de actividad', 'violet'),
            ]);

        $trainings = $connection->table('capacitaciones')
            ->select(['nombre', 'fecha_inicio', 'fecha_final'])
            ->where('activo', 1)
            ->where(function ($query) use ($firstDay, $lastDay): void {
                $query->whereBetween('fecha_inicio', [$firstDay, $lastDay])
                    ->orWhereBetween('fecha_final', [$firstDay, $lastDay]);
            })
            ->get()
            ->flatMap(fn ($row) => [
                $this->event($row->fecha_inicio, $row->nombre, 'Inicio de capacitación', 'teal'),
                $this->event($row->fecha_final, $row->nombre, 'Fin de capacitación', 'amber'),
            ]);

        return $activities->merge($trainings)
            ->filter()
            ->sortBy('date')
            ->values()
            ->all();
    }

    private function event(?string $date, ?string $title, string $kind, string $tone): ?array
    {
        if (!$date) {
            return null;
        }

        return [
            'date' => substr($date, 0, 10),
            'title' => $title ?: 'Sin nombre',
            'kind' => $kind,
            'tone' => $tone,
        ];
    }

    private function monthBounds(string $month): array
    {
        $start = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $month);
        abort_unless($start && $start->format('Y-m') === $month, 422, 'El mes debe tener el formato YYYY-MM.');

        return [$start->startOfMonth()->toDateString(), $start->endOfMonth()->toDateString()];
    }
}
