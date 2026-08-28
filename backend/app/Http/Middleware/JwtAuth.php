<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class AuthenticateJwt
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'msg' => 'token_missing',
                'detail' => 'Token no proporcionado'
            ], 401);
        }

        $jwtAuth = new JwtAuth();
        $token = str_replace('Bearer ', '', $token);
        
        if (!$jwtAuth->checkToken($token)) {
            return response()->json([
                'msg' => 'token_invalid',
                'detail' => 'Token inválido o expirado'
            ], 401);
        }

        return $next($request);
    }
}
