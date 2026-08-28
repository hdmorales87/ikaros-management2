<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtAuth
{
    private string $key;
    private int $ttl;

    public function __construct()
    {
        $this->key = config('app.jwt_secret', env('JWT_SECRET', 'your-default-secret-key'));
        $this->ttl = (int) config('app.jwt_ttl', env('JWT_TTL', 28800)); // 8 hours default
    }

    public function signup(object $user, ?bool $getToken = null): object|array
    {
        try {
            if ($user) {
                $now = time();
                $token = [
                    'sub' => $user->userData->id,
                    'userData' => $user->userData,
                    'companyData' => $user->companyData,
                    'permisos' => $user->permisos,
                    'modulos' => $user->modulos,
                    'iat' => $now,
                    'exp' => $now + $this->ttl,
                ];

                $jwt = JWT::encode($token, $this->key, 'HS256');
                
                if (is_null($getToken)) {
                    return (object) [
                        'token' => $jwt,
                        'userData' => $user->userData,
                        'companyData' => $user->companyData,
                        'permisos' => $user->permisos,
                        'modulos' => $user->modulos,
                    ];
                }
                
                $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
                return $decoded;
            }

            return [
                'status' => 'error',
                'message' => 'Login Incorrecto',
                'code' => 401
            ];
        } catch (\Exception $e) {
            \Log::error("|JwtAuth|signup: '".$e->getMessage()."'");
            return [
                'status' => 'error',
                'message' => 'Error al generar token',
                'code' => 500
            ];
        }
    }

    public function checkToken(string $jwt, bool $getIdentity = false): bool|object
    {
        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));

            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
                return $getIdentity ? $decoded : true;
            }

            return false;
        } catch (ExpiredException $e) {
            \Log::error("|JwtAuth|checkToken: Token expirado - '".$e->getMessage()."'");
            return false;
        } catch (SignatureInvalidException $e) {
            \Log::error("|JwtAuth|checkToken: Firma inválida - '".$e->getMessage()."'");
            return false;
        } catch (\Exception $e) {
            \Log::error("|JwtAuth|checkToken: '".$e->getMessage()."'");
            return false;
        }
    }
}
