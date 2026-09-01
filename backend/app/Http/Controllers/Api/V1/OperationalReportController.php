<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\OperationalReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationalReportController extends Controller
{
    public function __construct(private readonly OperationalReportService $operationalReportService)
    {
    }

    public function requests(Request $request): JsonResponse
    {
        return response()->json($this->operationalReportService->requests((string) $request->header('x-uuid', '')));
    }
}
