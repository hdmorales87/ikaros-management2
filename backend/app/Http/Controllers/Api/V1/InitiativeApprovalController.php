<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InitiativeApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InitiativeApprovalController extends Controller
{
    public function __construct(private readonly InitiativeApprovalService $initiativeApprovalService)
    {
    }

    public function trace(Request $request): JsonResponse
    {
        return response()->json($this->initiativeApprovalService->trace((string) $request->header('x-uuid', '')));
    }
}
