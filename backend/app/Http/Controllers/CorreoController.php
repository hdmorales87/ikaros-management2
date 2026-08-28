<?php

namespace App\Http\Controllers;

use App\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorreoController extends Controller
{
    public function __construct(private readonly MailService $mailService)
    {
    }

    public function checkSMTP(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        return response()->json($this->mailService->check($data['email'], $this->uuid($request)));
    }

    public function emailPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'opcion' => ['required', 'string', 'in:reset,force'],
        ]);
        return response()->json($this->mailService->passwordReset($data['email'], $data['opcion'], $this->uuid($request), (string) config('app.url')));
    }

    public function enviarMailActivacion(Request $request): JsonResponse
    {
        $data = $request->validate(['idUser' => ['required', 'integer']]);
        return response()->json($this->mailService->activation($data['idUser'], $this->uuid($request), (string) config('app.url')));
    }

    private function uuid(Request $request): string
    {
        return (string) $request->header('x-uuid', '');
    }
}
