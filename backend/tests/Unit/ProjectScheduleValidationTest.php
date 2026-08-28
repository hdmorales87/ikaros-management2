<?php

namespace Tests\Unit;

use App\Services\DataGridService;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProjectScheduleValidationTest extends TestCase
{
    public function test_project_dates_cannot_exclude_existing_activities(): void
    {
        $connection = DB::connection();
        $this->createScheduleTables($connection);
        $connection->table('proyectos')->insert(['id' => 1, 'fecha_inicio' => '2026-01-01', 'fecha_final' => '2026-01-31']);
        $connection->table('proyectos_actividades')->insert(['id' => 1, 'id_proyecto' => 1, 'fecha_inicio' => '2026-01-10', 'fecha_final' => '2026-01-25']);

        $this->expectException(HttpException::class);
        $this->validateSchedule($connection, 'proyectos', ['fecha_inicio' => '2026-01-01', 'fecha_final' => '2026-01-20'], 1);
    }

    public function test_activity_dates_cannot_exclude_existing_subactivities(): void
    {
        $connection = DB::connection();
        $this->createScheduleTables($connection);
        $connection->table('proyectos')->insert(['id' => 1, 'fecha_inicio' => '2026-01-01', 'fecha_final' => '2026-01-31']);
        $connection->table('proyectos_actividades')->insert(['id' => 1, 'id_proyecto' => 1, 'fecha_inicio' => '2026-01-05', 'fecha_final' => '2026-01-25']);
        $connection->table('proyectos_subactividades')->insert(['id' => 1, 'id_actividad' => 1, 'fecha_inicio' => '2026-01-10', 'fecha_final' => '2026-01-22']);

        $this->expectException(HttpException::class);
        $this->validateSchedule($connection, 'proyectos_actividades', ['fecha_inicio' => '2026-01-05', 'fecha_final' => '2026-01-20'], 1);
    }

    private function validateSchedule(Connection $connection, string $table, array $data, int $id): void
    {
        $method = new \ReflectionMethod(DataGridService::class, 'validateProjectSchedule');
        $method->invoke(new DataGridService(), $connection, $table, $data, $id);
    }

    private function createScheduleTables(Connection $connection): void
    {
        Schema::dropIfExists('proyectos_subactividades');
        Schema::dropIfExists('proyectos_actividades');
        Schema::dropIfExists('proyectos');
        Schema::create('proyectos', function ($table): void { $table->increments('id'); $table->date('fecha_inicio'); $table->date('fecha_final'); });
        Schema::create('proyectos_actividades', function ($table): void { $table->increments('id'); $table->integer('id_proyecto'); $table->date('fecha_inicio'); $table->date('fecha_final'); });
        Schema::create('proyectos_subactividades', function ($table): void { $table->increments('id'); $table->integer('id_actividad'); $table->date('fecha_inicio'); $table->date('fecha_final'); });
    }
}