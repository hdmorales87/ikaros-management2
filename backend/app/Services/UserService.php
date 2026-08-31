<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtAuth;

class UserService
{
    public function listUsers(string $uuid): array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);

        return $connection->table('users')
            ->select(['id', 'primer_nombre as nombre', 'primer_apellido as apellido', 'email', 'id_rol', 'activo', 'acceso_sistema'])
            ->orderBy('primer_nombre')
            ->get()
            ->map(fn ($user) => (array) $user)
            ->all();
    }

    public function findUser(int $id, string $uuid): ?array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);
        $user = $connection->table('users')
            ->select(['id', 'primer_nombre as nombre', 'segundo_nombre', 'primer_apellido as apellido', 'segundo_apellido', 'email', 'id_rol', 'activo', 'acceso_sistema'])
            ->where('id', $id)
            ->first();

        return $user ? (array) $user : null;
    }

    public function createUser(array $data, string $uuid): array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);
        $data = $this->mapLegacyNameColumns($data);
        $data['password'] = Hash::make($data['password']);
        $data['fecha_cambio_password'] = now();
        $data['intentos_login'] = 0;
        $data['activo'] = $data['activo'] ?? 1;
        $data['acceso_sistema'] = $data['acceso_sistema'] ?? 1;
        $id = $connection->table('users')->insertGetId($data);

        return $this->findUser((int) $id, $uuid) ?? [];
    }

    public function updateUser(int $id, array $data, string $uuid): ?array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);
        $data = $this->mapLegacyNameColumns($data);
        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }
        $connection->table('users')->where('id', $id)->update($data);

        return $this->findUser($id, $uuid);
    }

    private function mapLegacyNameColumns(array $data): array
    {
        if (array_key_exists('nombre', $data)) {
            $data['primer_nombre'] = $data['nombre'];
            unset($data['nombre']);
        }

        if (array_key_exists('apellido', $data)) {
            $data['primer_apellido'] = $data['apellido'];
            unset($data['apellido']);
        }

        return $data;
    }

    public function deleteUser(int $id, string $uuid): bool
    {
        $connection = (new Company())->getConnectionByUUID($uuid);

        return $connection->table('users')->where('id', $id)->delete() > 0;
    }

    public function checkUsername(string $userName, string $uuid): array
    {
        try {
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);
            
            $user = $connection->table('users AS U')
                ->select('U.id', 'U.nombre', 'U.imagen_usuario', 'U.sexo')
                ->where('U.activo', 1)
                ->where('U.email', $userName)
                ->first();

            if ($user) {
                return [
                    'msg' => 'true',
                    'name_user' => $user->nombre,
                    'imagen_usuario' => $user->imagen_usuario,
                    'sexo' => (int) $user->sexo
                ];
            }

            return ['msg' => 'notExist'];
        } catch (\Exception $e) {
            \Log::error("|UserService|checkUsername: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function login(string $username, string $password, string $uuid): array
    {
        try {
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);
            $companyData = $company->getCompanyByUUID($uuid);

            if ($companyData->dias_vencimiento <= 0) {
                return [
                    'msg' => 'licence_expired',
                    'detail' => 'La licencia ha expirado'
                ];
            }

            $user = $connection->table('users')
                ->where('activo', 1)
                ->where('email', $username)
                ->first();

            if (!$user || !Hash::check($password, $user->password)) {
                return $this->acumularIntentosLogin($username, $uuid);
            }

            if ($user && !($user->id_rol > 0)) {
                return ['msg' => 'role_not_asigned', 'code' => 401];
            }

            $user = $connection->table('users as u')
                ->select(
                    'u.*',
                    DB::raw('IF(id_rol=1,0,DATEDIFF(NOW(),fecha_cambio_password)) AS dias_cambio'),
                    'rl.nombre AS rol'
                )
                ->join('roles as rl', 'rl.id', '=', 'u.id_rol')
                ->where('u.activo', 1)
                ->where('email', $username)
                ->first();

            if ($user) {
                if ($user->acceso_sistema === 'false') {
                    return ['msg' => 'inactive', 'code' => 401];
                }

                $policies = $connection->table('politicas_seguridad')
                    ->select('password_vencimiento')
                    ->where('activo', 1)
                    ->first();

                if ($policies) {
                    $dias_cambio = $user->dias_cambio;
                    $dias_vencimiento = $policies->password_vencimiento;

                    if ($dias_cambio >= $dias_vencimiento && $dias_vencimiento > 0) {
                        return ['msg' => 'vencido', 'code' => 401];
                    }

                    $connection->table('users')
                        ->where('id', $user->id)
                        ->update(['intentos_login' => 0]);
                }

                return (array) $user;
            }

            return $this->acumularIntentosLogin($username, $uuid);
        } catch (\Exception $e) {
            \Log::error("|UserService|login: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage(), 'code' => 500];
        }
    }

    public function updateToken(string $username, string $token, string $uuid): array
    {
        try {
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);
            
            $connection->table('users')
                ->where('activo', 1)
                ->where('email', $username)
                ->update([
                    'token' => $token,
                    'acceso_sistema' => 'false'
                ]);
                
            return ['msg' => 'success'];
        } catch (\Exception $e) {
            \Log::error("|UserService|updateToken: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function resetPassword(string $password, string $email, string $uuid): array
    {
        try {
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);
            
            $connection->table('users')
                ->where('activo', 1)
                ->where('email', $email)
                ->update([
                    'token' => '',
                    'password' => Hash::make($password),
                    'acceso_sistema' => 'true',
                    'fecha_cambio_password' => now(),
                    'intentos_login' => 0
                ]);
                
            return ['msg' => 'success'];
        } catch (\Exception $e) {
            \Log::error("|UserService|resetPassword: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function updatePassword(\Illuminate\Http\Request $request): array
    {
        try {
            $request->uuid = $request->header('x-uuid');
            $company = new Company();
            $connection = $company->getConnectionByUUID($request->uuid);
            
            $row = $connection->table('users')
                ->select('password')
                ->where('activo', 1)
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if ($row) {
                return $this->resetPassword($request->password, $request->email, $request->uuid);
            }

            return ['msg' => 'error', 'detail' => 'No hay informacion relacionada!'];
        } catch (\Exception $e) {
            \Log::error("|UserService|updatePassword: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public function acumularIntentosLogin(string $username, string $uuid): array
    {
        try {
            $company = new Company();
            $connection = $company->getConnectionByUUID($uuid);
            
            $results = $connection->table('users AS U')
                ->select('U.intentos_login', 'PS.intentos_login AS intentos_permitidos')
                ->leftJoin('politicas_seguridad AS PS', 'PS.activo', '=', 'U.activo')
                ->where('u.email', $username)
                ->first();

            if ($results) {
                if ($results->intentos_permitidos > 0) {
                    $intentos_actuales = $results->intentos_login + 1;
                    $updateUser = ['intentos_login' => $intentos_actuales];
                    $mensaje = $results->intentos_permitidos - $intentos_actuales;

                    if ($intentos_actuales >= $results->intentos_permitidos) {
                        $updateUser['acceso_sistema'] = 'false';
                        $mensaje = 'bloqueado';
                    }

                    $connection->table('users')
                        ->where('email', $username)
                        ->update($updateUser);

                    return ['msg' => $mensaje, 'code' => 401];
                }
                return ['msg' => 'notExist', 'code' => 401];
            }

            return ['msg' => 'notExist', 'code' => 401];
        } catch (\Exception $e) {
            \Log::error("|UserService|acumularIntentosLogin: '".$e->getMessage()."'");
            return ['msg' => 'error', 'detail' => $e->getMessage(), 'code' => 500];
        }
    }

    public function checkUserToken(\Illuminate\Http\Request $request): string
    {
        try {
            $request->uuid = $request->header('x-uuid');
            $company = new Company();
            $connection = $company->getConnectionByUUID($request->uuid);
            
            $count = $connection->table('users')
                ->select(DB::raw("COUNT(*) AS cuenta"))
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            return json_encode($count);
        } catch (\Exception $e) {
            \Log::error("|UserService|checkUserToken: '".$e->getMessage()."'");
            return json_encode(['msg' => 'error', 'detail' => $e->getMessage()]);
        }
    }
}
