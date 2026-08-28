<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;
use Illuminate\Http\Request;

class AuthenticateJwt
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = (string) $request->header('Authorization');
        if ($authorization === '') {
            return response()->json(['msg' => 'token_missing', 'detail' => 'Token no proporcionado'], 401);
        }

        $token = str_replace('Bearer ', '', $authorization);
        if (!(new JwtAuth())->checkToken($token)) {
            return response()->json(['msg' => 'token_invalid', 'detail' => 'Token inválido o expirado'], 401);
        }

        return $next($request);
    }
}
