<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SwaggerAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Inicia sesión",
     *     security={{}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", example="secret"),
     *             @OA\Property(property="company", type="string", example="empresa1", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login correcto"),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function loginExample(): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
