<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProjectHoursService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectHoursController extends Controller
{
    public function __construct(private readonly ProjectHoursService $projectHoursService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->projectHoursService->list((string) $request->header('x-uuid', '')));
    }
}
