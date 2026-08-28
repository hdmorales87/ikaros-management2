<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.default');
        $tables = [
            'applications', 'chatbot_options_maestro', 'companies', 'companies_adjuntos',
            'companies_chatbot_options', 'companies_modulos', 'document_types', 'modulos',
        ];

        foreach ($tables as $table) {
            if (!Schema::connection($connection)->hasTable($table)) {
                throw new RuntimeException("La tabla global requerida [{$table}] no existe en la conexión [{$connection}].");
            }
        }
    }

    public function down(): void
    {
        // La línea base representa un esquema existente y no debe eliminar tablas.
    }
};