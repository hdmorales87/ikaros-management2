<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SwaggerExampleController extends Controller
{
    /**
     * @OA\Info(
     *     title="Ikaros Management API",
     *     version="1.0.0",
     *     description="Documentación OpenAPI del backend Ikaros Management 2."
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/checkUserToken",
     *     tags={"Auth"},
     *     summary="Valida la sesión del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token válido"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function exampleTokenCheck(): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
