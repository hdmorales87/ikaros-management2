<?php

namespace App\Http\Controllers;

/**
 * @OA\OpenApi(
 *     security={{"bearerAuth": {}}},
 *     @OA\Info(
 *         title="Ikaros Management API",
 *         version="1.0.0",
 *         description="Documentación OpenAPI del backend de Ikaros Management 2. Generada a partir de las rutas y controladores actuales del proyecto."
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8000",
 *         description="Servidor local de desarrollo"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="JWT incluido en el header Authorization: Bearer <token>"
 *     )
 * )
 */
class OpenApiDocsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/checkCompany",
     *     tags={"Auth"},
     *     summary="Verifica si una empresa existe para el sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documento"},
     *             @OA\Property(property="documento", type="string", example="900123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Empresa válida"),
     *     @OA\Response(response=422, description="Datos de validación inválidos")
     * )
     */
    public function checkCompanyDoc() {}

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Autentica un usuario y devuelve el token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", example="secret"),
     *             @OA\Property(property="company", type="string", example="empresa-demo", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login exitoso"),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function loginDoc() {}

    /**
     * @OA\Get(
     *     path="/api/checkUserToken",
     *     tags={"Auth"},
     *     summary="Valida si el token del usuario es activo",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Token válido"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function checkUserTokenDoc() {}

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Lista todos los usuarios de la empresa actual",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Listado de usuarios")
     * )
     */
    public function listUsersDoc() {}

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Obtiene un usuario por su id",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Identificador del usuario", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Usuario encontrado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function showUserDoc() {}

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Crea un nuevo usuario",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "apellido", "email", "password", "id_rol"},
     *             @OA\Property(property="nombre", type="string", example="Ana"),
     *             @OA\Property(property="apellido", type="string", example="García"),
     *             @OA\Property(property="email", type="string", format="email", example="ana@empresa.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="id_rol", type="integer", example=2),
     *             @OA\Property(property="activo", type="boolean", example=true),
     *             @OA\Property(property="acceso_sistema", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario creado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function storeUserDoc() {}

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Actualiza un usuario existente",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Ana"),
     *             @OA\Property(property="apellido", type="string", example="García"),
     *             @OA\Property(property="email", type="string", format="email", example="ana@empresa.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="id_rol", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario actualizado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function updateUserDoc() {}

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Elimina un usuario",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Usuario eliminado"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroyUserDoc() {}

    /**
     * @OA\Get(
     *     path="/api/getCompanyData",
     *     tags={"Company"},
     *     summary="Obtiene los datos de la empresa autenticada",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Datos de la empresa")
     * )
     */
    public function getCompanyDataDoc() {}

    /**
     * @OA\Get(
     *     path="/api/getCompanyModules",
     *     tags={"Company"},
     *     summary="Obtiene los módulos activos para la empresa actual",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Módulos disponibles")
     * )
     */
    public function getCompanyModulesDoc() {}

    /**
     * @OA\Post(
     *     path="/api/guardaPermisos",
     *     tags={"Roles"},
     *     summary="Guarda los permisos asociados a un rol",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idRol", "arrayPermisos"},
     *             @OA\Property(property="idRol", type="integer", example=1),
     *             @OA\Property(property="arrayPermisos", type="array", @OA\Items(type="integer"), example={1,2,3})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Permisos guardados")
     * )
     */
    public function guardaPermisosDoc() {}

    /**
     * @OA\Get(
     *     path="/api/roles/{idRol}/permisos",
     *     tags={"Roles"},
     *     summary="Obtiene los permisos de un rol",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="idRol", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Permisos del rol")
     * )
     */
    public function permisosRolDoc() {}

    /**
     * @OA\Get(
     *     path="/api/getConocimientos",
     *     tags={"Knowledge"},
     *     summary="Lista conocimientos del sistema",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Listado de conocimientos")
     * )
     */
    public function getConocimientosDoc() {}

    /**
     * @OA\Post(
     *     path="/api/guardarSolicitud",
     *     tags={"Requests"},
     *     summary="Guarda o actualiza una solicitud",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Solicitud guardada")
     * )
     */
    public function guardarSolicitudDoc() {}

    /**
     * @OA\Post(
     *     path="/api/getDataGrid",
     *     tags={"DataGrid"},
     *     summary="Obtiene una grilla de datos configurable",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Datos obtenidos")
     * )
     */
    public function getDataGridDoc() {}

    /**
     * @OA\Post(
     *     path="/api/uploaderFile",
     *     tags={"Files"},
     *     summary="Sube un archivo al sistema",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Archivo subido")
     * )
     */
    public function uploaderFileDoc() {}

    /**
     * @OA\Get(
     *     path="/api/files",
     *     tags={"Files"},
     *     summary="Lista archivos del sistema",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Archivos encontrados")
     * )
     */
    public function listFilesDoc() {}

    /**
     * @OA\Get(
     *     path="/api/downloadFile/{uuid}/{folder}/{file}",
     *     tags={"Files"},
     *     summary="Descarga un archivo del sistema",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Archivo descargado")
     * )
     */
    public function downloadFileDoc() {}

    /**
     * @OA\Post(
     *     path="/api/checkIMAP",
     *     tags={"Mail"},
     *     summary="Valida la configuración IMAP del tenant",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Configuración IMAP válida")
     * )
     */
    public function checkImapDoc() {}
}
