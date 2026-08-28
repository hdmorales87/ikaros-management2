<?php

namespace App\Http\Controllers;

use App\Services\FileManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    public function __construct(private readonly FileManagerService $fileManagerService)
    {
    }

    public function obtenerConsumoAlmacenamiento(Request $request): JsonResponse
    {
        return response()->json($this->fileManagerService->storageUsage($this->uuid($request)));
    }

    public function uploaderFile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'folder' => ['required', 'string', 'max:150'],
            'id' => ['required', 'string', 'max:100'],
        ]);
        $filename = $this->fileManagerService->upload(
            $data['file'],
            $this->uuid($request),
            $data['folder'],
            $data['id'],
        );

        return response()->json(['msg' => 'success', 'detail' => $filename]);
    }

    public function downloadFile(Request $request, string $uuid, string $folder, string $file): Response
    {
        abort_unless($uuid === (string) $request->header('x-uuid', ''), 403, 'Empresa no autorizada.');
        $path = $this->fileManagerService->path($uuid, $folder, $file);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, basename($file));
    }

    public function deleteFile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'folder' => ['required', 'string', 'max:150'],
            'file' => ['required', 'string', 'max:255'],
            'table' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]+$/'],
            'id' => ['required', 'integer'],
        ]);
        $this->fileManagerService->delete(
            $this->uuid($request),
            $data['folder'],
            $data['file'],
            $data['table'],
            $data['id'],
        );

        return response()->json(['msg' => 'success', 'detail' => $data['file']]);
    }

    public function listFiles(Request $request): JsonResponse
    {
        $data = $request->validate(['folder' => ['required', 'string', 'max:150']]);
        return response()->json($this->fileManagerService->list($this->uuid($request), $data['folder']));
    }

    public function importExcelDatabase(Request $request): JsonResponse
    {
        $data = $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], 'table' => ['required', 'string', 'in:users,activos']]);
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new \App\Helpers\JwtAuth())->checkToken($token, true);
        abort_unless(is_object($identity) && (int) ($identity->userData->id_rol ?? 0) === 1, 403, 'Usuario no autorizado.');
        return response()->json($this->fileManagerService->importExcel($data['table'], $data['file']->getRealPath(), $this->uuid($request), (int) ($identity->sub ?? 0)));
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
