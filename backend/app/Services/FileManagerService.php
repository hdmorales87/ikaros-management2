<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class FileManagerService
{
    public function storageUsage(string $uuid): float
    {
        $root = Storage::disk('public')->path($this->safePath($uuid));
        if (!is_dir($root)) {
            return 0.0;
        }

        $bytes = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $bytes += $file->getSize();
            }
        }

        return round($bytes / 1024 / 1024, 2);
    }

    public function upload(UploadedFile $file, string $uuid, string $folder, string $recordId): string
    {
        $directory = $this->safePath($uuid.'/'.$folder);
        $filename = basename($recordId).'_'.Str::uuid().'.'.$file->extension();
        Storage::disk('public')->putFileAs($directory, $file, $filename);

        return $filename;
    }

    public function delete(string $uuid, string $folder, string $filename, string $table, int $id): bool
    {
        $path = $this->safePath($uuid.'/'.$folder.'/'.basename($filename));
        Storage::disk('public')->delete($path);

        return (new Company())->getConnectionByUUID($uuid)
            ->table($table)
            ->where('id', $id)
            ->delete() > 0;
    }

    public function path(string $uuid, string $folder, string $filename): string
    {
        return $this->safePath($uuid.'/'.$folder.'/'.basename($filename));
    }

    public function list(string $uuid, string $folder): array
    {
        $directory = $this->safePath($uuid.'/'.$folder);
        return array_map(fn ($path) => basename($path), Storage::disk('public')->files($directory));
    }

    public function importExcel(string $table, string $path, string $uuid, int $userId): array
    {
        abort_unless(in_array($table, ['users', 'activos'], true), 422, 'Tabla de importación no permitida.');
        $connection = (new Company())->getConnectionByUUID($uuid);
        $success = [];
        $errors = [];
        $line = 0;
        foreach (SimpleExcelReader::create($path)->getRows() as $row) {
            $line++;
            if ($line > 500) break;
            try {
                $row = array_filter($row, fn ($value, $key) => $key !== '', ARRAY_FILTER_USE_BOTH);
                if ($table === 'users') {
                    abort_unless(filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL), 422, 'Email inválido.');
                    abort_unless(!empty($row['documento']) && !empty($row['primer_nombre']), 422, 'Datos obligatorios faltantes.');
                    $row['nombre'] = strtoupper((string) $row['primer_nombre']);
                    $row['apellido'] = strtoupper((string) ($row['primer_apellido'] ?? ''));
                    $row['password'] = Hash::make((string) ($row['password'] ?? 'ChangeMe123!'));
                    unset($row['primer_nombre'], $row['segundo_nombre'], $row['primer_apellido'], $row['segundo_apellido']);
                    $row['id_rol'] = (int) ($row['id_rol'] ?? 0) === 1 ? 0 : (int) ($row['id_rol'] ?? 0);
                    $row['activo'] = 1;
                }
                $id = $connection->table($table)->insertGetId($row);
                $success[] = ['id' => $id, 'row' => $row];
            } catch (\Throwable $exception) {
                $errors[] = ['row' => $row, 'detail' => $exception->getMessage()];
            }
        }
        return ['code' => 200, 'success' => $success, 'errors' => $errors];
    }

    private function safePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path, '/'));
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            throw new \InvalidArgumentException('Ruta de archivo inválida.');
        }

        return $path;
    }
}
