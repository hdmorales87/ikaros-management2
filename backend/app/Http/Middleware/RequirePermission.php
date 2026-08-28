<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, int $permission)
    {
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new JwtAuth())->checkToken($token, true);
        $permissions = array_filter(array_map('intval', explode(',', (string) ($identity->permisos ?? ''))));
        $isSuperUser = (int) ($identity->userData->id_rol ?? 0) === 1 || in_array(1, $permissions, true);

        abort_unless($isSuperUser || in_array($permission, $permissions, true), 403, 'Usuario no autorizado.');

        return $next($request);
    }
}
