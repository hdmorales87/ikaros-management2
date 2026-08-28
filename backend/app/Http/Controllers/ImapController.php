<?php

namespace App\Http\Controllers;

use App\Services\ImapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImapController extends Controller
{
    public function __construct(private readonly ImapService $imapService)
    {
    }

    public function test(Request $request): JsonResponse
    {
        return response()->json($this->imapService->test((string) $request->header('x-uuid', '')));
    }
}
