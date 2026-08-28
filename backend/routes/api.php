<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::middleware('security.headers')->group(function () {
    Route::post('/checkCompany', [App\Http\Controllers\CompanyController::class, 'checkCompany']);
    Route::get('/checkUsername/{username}', [App\Http\Controllers\UserController::class, 'checkUsername']);
    Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
    Route::get('/checkUserToken', [App\Http\Controllers\UserController::class, 'checkUserToken']);
    Route::post('/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::post('/emailPassword', [App\Http\Controllers\CorreoController::class, 'emailPassword']);
    Route::get('/getPoliticasSeguridad', [App\Http\Controllers\CompanyController::class, 'getPoliticasSeguridad']);
    Route::get('/getIdioma', [App\Http\Controllers\CompanyController::class, 'getIdioma']);
    Route::get('/getEncuestaTercero', [App\Http\Controllers\TerceroController::class, 'getEncuestaTercero']);
    Route::get('/getTerceroById', [App\Http\Controllers\TerceroController::class, 'getTerceroById']);
    Route::get('/getPreguntasEncuestaTercero', [App\Http\Controllers\TerceroController::class, 'getPreguntasEncuestaTercero']);
    Route::post('/guardarEncuestaTercero', [App\Http\Controllers\TerceroController::class, 'guardarEncuestaTercero']);
    Route::post('/linkEncuestaCliente', [App\Http\Controllers\TerceroController::class, 'linkEncuestaCliente']);
    Route::get('/cargarEncuesta', [App\Http\Controllers\SolicitudController::class, 'verificarEncuesta']);
    Route::post('/guardarEncuesta', [App\Http\Controllers\SolicitudController::class, 'guardarEncuesta']);
    Route::get('/getEstadoSolicitudValidacion', [App\Http\Controllers\SolicitudController::class, 'getEstadoSolicitudValidacion']);
});

// Protected routes (JWT authentication required)
Route::middleware(['security.headers', 'authenticate.jwt'])->group(function () {
    // Company routes
    Route::post('/sincronizarEmpresa', [App\Http\Controllers\CompanyController::class, 'sincronizarEmpresa']);
    Route::post('/guardaModulos', [App\Http\Controllers\CompanyController::class, 'guardaModulos'])->middleware('permission:19');
    Route::post('/crearDirectorios', [App\Http\Controllers\CompanyController::class, 'crearDirectorios']);
    Route::get('/getCompanyData', [App\Http\Controllers\CompanyController::class, 'getCompanyData']);
    Route::get('/getCompanyModules', [App\Http\Controllers\CompanyController::class, 'getCompanyModules']);
    Route::get('/getChatbotMenu', [App\Http\Controllers\CompanyController::class, 'getChatbotMenu']);
    
    // User routes
    Route::middleware('permission:22')->group(function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('/users/{id}', [App\Http\Controllers\UserController::class, 'show']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
    });

    Route::get('/getSolicitudesUrgencias', [App\Http\Controllers\SolicitudController::class, 'getSolicitudesUrgencias']);
    Route::get('/getSolicitudesImpactos', [App\Http\Controllers\SolicitudController::class, 'getSolicitudesImpactos']);
    Route::get('/getAreasServicioByModulo/{modulo}', [App\Http\Controllers\SolicitudController::class, 'getAreasServicioByModulo']);
    Route::get('/getCategoriasByAreaServicio/{area}', [App\Http\Controllers\SolicitudController::class, 'getCategoriasByAreaServicio']);
    Route::get('/getSubcategoriasByCategoria/{categoria}', [App\Http\Controllers\SolicitudController::class, 'getSubcategoriasByCategoria']);
    Route::post('/guardarSolicitud', [App\Http\Controllers\SolicitudController::class, 'guardarSolicitud']);
    Route::get('/verificarEstadoSolicitud', [App\Http\Controllers\SolicitudController::class, 'verificarEstadoSolicitud']);
    Route::post('/rechazarSolucion', [App\Http\Controllers\SolicitudController::class, 'rechazarSolucion']);
    Route::post('/recategorizacionIncidencia', [App\Http\Controllers\SolicitudController::class, 'recategorizacionIncidencia']);
    Route::post('/procesoGestion', [App\Http\Controllers\SolicitudController::class, 'procesoGestion']);
    Route::post('/asignarSolicitud', [App\Http\Controllers\SolicitudController::class, 'asignarSolicitud']);
    Route::post('/guardarValidacionIniciativa', [App\Http\Controllers\SolicitudController::class, 'guardarValidacionIniciativa']);
    Route::post('/guardarValidacionIniciativaSeguimiento', [App\Http\Controllers\SolicitudController::class, 'guardarValidacionIniciativaSeguimiento']);
    Route::get('/getConocimientos', [App\Http\Controllers\ConocimientoController::class, 'getConocimientos']);
    Route::get('/getConocimientosByDescripcion', [App\Http\Controllers\ConocimientoController::class, 'getConocimientosByDescripcion']);
    Route::get('/getConocimientoById/{id}', [App\Http\Controllers\ConocimientoController::class, 'getConocimientoById']);
    Route::post('/guardarReputacionConocimiento', [App\Http\Controllers\ConocimientoController::class, 'guardarReputacionConocimiento']);
    Route::post('/guardaPermisos', [App\Http\Controllers\RoleController::class, 'guardaPermisos'])->middleware('permission:32');
    Route::get('/roles/{idRol}/permisos', [App\Http\Controllers\RoleController::class, 'permisos'])->middleware('permission:32');
    Route::get('/obtenerConsumoAlmacenamiento', [App\Http\Controllers\FileManagerController::class, 'obtenerConsumoAlmacenamiento']);
    Route::post('/uploaderFile', [App\Http\Controllers\FileManagerController::class, 'uploaderFile']);
    Route::post('/deleteFile', [App\Http\Controllers\FileManagerController::class, 'deleteFile']);
    Route::get('/files', [App\Http\Controllers\FileManagerController::class, 'listFiles']);
    Route::get('/downloadFile/{uuid}/{folder}/{file}', [App\Http\Controllers\FileManagerController::class, 'downloadFile']);
    Route::post('/importExcelDatabase', [App\Http\Controllers\FileManagerController::class, 'importExcelDatabase']);
    Route::post('/getDataGrid', [App\Http\Controllers\DataGridController::class, 'getData']);
    Route::post('/dataGrid', [App\Http\Controllers\DataGridController::class, 'insertData']);
    Route::put('/dataGrid', [App\Http\Controllers\DataGridController::class, 'updateData']);
    Route::delete('/dataGrid', [App\Http\Controllers\DataGridController::class, 'deleteData']);
    Route::post('/generarCodigoActivo', [App\Http\Controllers\ActivoController::class, 'generarCodigoActivo']);
    Route::post('/generarCodigosActivos', [App\Http\Controllers\ActivoController::class, 'generarCodigosActivos']);
    Route::post('/guardarCamposFicha', [App\Http\Controllers\FichaTecnicaController::class, 'guardarCamposFicha']);
    Route::get('/camposFicha', [App\Http\Controllers\FichaTecnicaController::class, 'campos']);
    Route::get('/valoresFicha', [App\Http\Controllers\FichaTecnicaController::class, 'valores']);
    Route::post('/checkSMTP', [App\Http\Controllers\CorreoController::class, 'checkSMTP']);
    Route::post('/checkIMAP', [App\Http\Controllers\ImapController::class, 'test'])->middleware('permission:51');
    Route::post('/enviarMailActivacion', [App\Http\Controllers\CorreoController::class, 'enviarMailActivacion']);
    Route::post('/notificarRiesgo', [App\Http\Controllers\NotificationController::class, 'risk']);
    Route::post('/notificarResponsableActividad', [App\Http\Controllers\NotificationController::class, 'activity']);
    Route::post('/notificarCambiosTabla', [App\Http\Controllers\NotificationController::class, 'tableChanges']);
    Route::post('/notificarComite', [App\Http\Controllers\NotificationController::class, 'committee']);
    Route::post('/notificarValidacionIniciativa', [App\Http\Controllers\NotificationController::class, 'initiative']);
    Route::post('/notificarAsistentesCapacitacion', [App\Http\Controllers\NotificationController::class, 'training']);
    Route::post('/notificarValidacionHoras', [App\Http\Controllers\NotificationController::class, 'hours']);
    Route::post('/notificarConfirmacionHoras', [App\Http\Controllers\NotificationController::class, 'confirmedHours']);
});
