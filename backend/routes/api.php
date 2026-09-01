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
    Route::prefix('v1')->group(function () {
        Route::get('/dashboard/summary', [App\Http\Controllers\Api\V1\DashboardController::class, 'summary']);
        Route::get('/calendar/events', [App\Http\Controllers\Api\V1\CalendarController::class, 'events']);
        Route::get('/third-party-survey-questions', [App\Http\Controllers\Api\V1\ThirdPartySurveyQuestionController::class, 'index'])->middleware('permission:36');
        Route::post('/third-party-survey-questions', [App\Http\Controllers\Api\V1\ThirdPartySurveyQuestionController::class, 'store'])->middleware('permission:36');
        Route::put('/third-party-survey-questions/{question}', [App\Http\Controllers\Api\V1\ThirdPartySurveyQuestionController::class, 'update'])->middleware('permission:36');
        Route::delete('/third-party-survey-questions/{question}', [App\Http\Controllers\Api\V1\ThirdPartySurveyQuestionController::class, 'destroy'])->middleware('permission:36');
        Route::get('/locations', [App\Http\Controllers\Api\V1\LocationController::class, 'index'])->middleware('permission:55');
        Route::post('/locations', [App\Http\Controllers\Api\V1\LocationController::class, 'store'])->middleware('permission:55');
        Route::put('/locations/{location}', [App\Http\Controllers\Api\V1\LocationController::class, 'update'])->middleware('permission:55');
        Route::delete('/locations/{location}', [App\Http\Controllers\Api\V1\LocationController::class, 'destroy'])->middleware('permission:55');
        Route::get('/imap/accounts/{account}/rules', [App\Http\Controllers\Api\V1\ImapRuleController::class, 'index'])->middleware('permission:51');
        Route::post('/imap/accounts/{account}/rules', [App\Http\Controllers\Api\V1\ImapRuleController::class, 'store'])->middleware('permission:51');
        Route::put('/imap/accounts/{account}/rules/{rule}', [App\Http\Controllers\Api\V1\ImapRuleController::class, 'update'])->middleware('permission:51');
        Route::delete('/imap/accounts/{account}/rules/{rule}', [App\Http\Controllers\Api\V1\ImapRuleController::class, 'destroy'])->middleware('permission:51');
        Route::get('/trainings', [App\Http\Controllers\Api\V1\TrainingController::class, 'index'])->middleware('permission:72');
        Route::post('/trainings', [App\Http\Controllers\Api\V1\TrainingController::class, 'store'])->middleware('permission:72');
        Route::get('/trainings/{training}', [App\Http\Controllers\Api\V1\TrainingController::class, 'show'])->middleware('permission:72');
        Route::put('/trainings/{training}', [App\Http\Controllers\Api\V1\TrainingController::class, 'update'])->middleware('permission:72');
        Route::delete('/trainings/{training}', [App\Http\Controllers\Api\V1\TrainingController::class, 'destroy'])->middleware('permission:72');
        Route::get('/trainings/{training}/attendees', [App\Http\Controllers\Api\V1\TrainingController::class, 'attendees'])->middleware('permission:72');
        Route::post('/trainings/{training}/attendees', [App\Http\Controllers\Api\V1\TrainingController::class, 'addAttendee'])->middleware('permission:72');
        Route::put('/trainings/{training}/attendees/{attendee}', [App\Http\Controllers\Api\V1\TrainingController::class, 'updateAttendance'])->middleware('permission:72');
        Route::delete('/trainings/{training}/attendees/{attendee}', [App\Http\Controllers\Api\V1\TrainingController::class, 'removeAttendee'])->middleware('permission:72');
        Route::get('/initiative-approvals', [App\Http\Controllers\Api\V1\InitiativeApprovalController::class, 'trace']);
        Route::get('/initiatives', [App\Http\Controllers\Api\V1\InitiativeController::class, 'index']);
        Route::post('/initiatives', [App\Http\Controllers\Api\V1\InitiativeController::class, 'store']);
        Route::put('/initiatives/{initiative}', [App\Http\Controllers\Api\V1\InitiativeController::class, 'update']);
        Route::get('/role-management', [App\Http\Controllers\RoleController::class, 'managementData'])->middleware('permission:32');
        Route::get('/project-hours', [App\Http\Controllers\Api\V1\ProjectHoursController::class, 'index'])->middleware('permission:91');
        Route::get('/operational-reports/requests', [App\Http\Controllers\Api\V1\OperationalReportController::class, 'requests'])->middleware('permission:18');
        foreach ([
            'service-areas' => 34,
            'departments' => 55,
            'service-categories' => 34,
            'service-subcategories' => 34,
            'asset-types' => 33,
            'currencies' => 57,
            'documentation-types' => 56,
            'file-extensions' => 87,
            'satisfaction-questions' => 36,
            'contract-states' => 36,
            'payment-plans' => 36,
            'holidays' => 96,
            'risk-probabilities' => 102,
            'risk-impacts' => 103,
            'imap-accounts' => 51,
        ] as $catalog => $permission) {
            Route::prefix('/configuration/'.$catalog)->middleware('permission:'.$permission)->group(function () use ($catalog): void {
                Route::get('/', [App\Http\Controllers\Api\V1\CatalogController::class, 'index'])->defaults('catalog', $catalog);
                Route::post('/', [App\Http\Controllers\Api\V1\CatalogController::class, 'store'])->defaults('catalog', $catalog);
                Route::put('/{item}', [App\Http\Controllers\Api\V1\CatalogController::class, 'update'])->defaults('catalog', $catalog);
                Route::delete('/{item}', [App\Http\Controllers\Api\V1\CatalogController::class, 'deactivate'])->defaults('catalog', $catalog);
            });
        }
        Route::get('/contract-notifications', [App\Http\Controllers\Api\V1\ContractController::class, 'notifications'])->middleware('permission:36');
        Route::get('/assets', [App\Http\Controllers\Api\V1\ActivoController::class, 'index'])->middleware('permission:11');
        Route::get('/assets/form-options', [App\Http\Controllers\Api\V1\ActivoController::class, 'formOptions'])->middleware('permission:11');
        Route::get('/assets/{asset}', [App\Http\Controllers\Api\V1\ActivoController::class, 'show'])->middleware('permission:11');
        Route::put('/assets/{asset}', [App\Http\Controllers\Api\V1\ActivoController::class, 'update'])->middleware('permission:11');
        Route::get('/incidents', [App\Http\Controllers\SolicitudController::class, 'indexIncidents'])->middleware('permission:2');
        Route::get('/incidents/{incident}', [App\Http\Controllers\SolicitudController::class, 'showIncident'])->middleware('permission:2');
        Route::get('/incidents/{incident}/followups', [App\Http\Controllers\SolicitudController::class, 'incidentFollowups'])->middleware('permission:2');
        Route::post('/incidents/{incident}/assignments', [App\Http\Controllers\SolicitudController::class, 'assignIncident'])->middleware('permission:2');
        Route::post('/incidents/{incident}/start-processing', [App\Http\Controllers\SolicitudController::class, 'startIncident'])->middleware('permission:2');
        Route::get('/problems', [App\Http\Controllers\SolicitudController::class, 'indexProblems'])->middleware('permission:5');
        Route::post('/problems/{problem}/assignments', [App\Http\Controllers\SolicitudController::class, 'assignProblem'])->middleware('permission:5');
        Route::post('/problems/{problem}/start-processing', [App\Http\Controllers\SolicitudController::class, 'startProblem'])->middleware('permission:5');
        Route::get('/services', [App\Http\Controllers\SolicitudController::class, 'indexServices'])->middleware('permission:7');
        Route::get('/services/{service}', [App\Http\Controllers\SolicitudController::class, 'showService'])->middleware('permission:7');
        Route::get('/services/{service}/followups', [App\Http\Controllers\SolicitudController::class, 'serviceFollowups'])->middleware('permission:7');
        Route::post('/services/{service}/assignments', [App\Http\Controllers\SolicitudController::class, 'assignService'])->middleware('permission:7');
        Route::post('/services/{service}/start-processing', [App\Http\Controllers\SolicitudController::class, 'startService'])->middleware('permission:7');
        Route::get('/clients', [App\Http\Controllers\TerceroController::class, 'indexClients'])->middleware('permission:62');
        Route::get('/providers', [App\Http\Controllers\TerceroController::class, 'indexProviders'])->middleware('permission:14');
        Route::get('/clients/{client}/contract-form-options', [App\Http\Controllers\Api\V1\ContractController::class, 'formOptionsForClient'])->middleware('permission:62');
        Route::get('/clients/{client}/contracts', [App\Http\Controllers\Api\V1\ContractController::class, 'indexForClient'])->middleware('permission:62');
        Route::post('/clients/{client}/contracts', [App\Http\Controllers\Api\V1\ContractController::class, 'storeForClient'])->middleware('permission:62');
        Route::put('/clients/{client}/contracts/{contract}', [App\Http\Controllers\Api\V1\ContractController::class, 'updateForClient'])->middleware('permission:62');
        Route::delete('/clients/{client}/contracts/{contract}', [App\Http\Controllers\Api\V1\ContractController::class, 'deactivateForClient'])->middleware('permission:62');
        Route::get('/clients/{client}/contracts/{contract}/attachments', [App\Http\Controllers\Api\V1\ContractController::class, 'attachmentsForClient'])->middleware('permission:62');
        Route::post('/clients/{client}/contracts/{contract}/attachments', [App\Http\Controllers\Api\V1\ContractController::class, 'storeAttachmentForClient'])->middleware('permission:62');
        Route::get('/clients/{client}/contracts/{contract}/attachments/{attachment}/download', [App\Http\Controllers\Api\V1\ContractController::class, 'downloadAttachmentForClient'])->middleware('permission:62');
        Route::get('/clients/{client}/contracts/{contract}/payments', [App\Http\Controllers\Api\V1\ContractController::class, 'paymentsForClient'])->middleware('permission:62');
        Route::post('/clients/{client}/contracts/{contract}/payments', [App\Http\Controllers\Api\V1\ContractController::class, 'storePaymentForClient'])->middleware('permission:62');
        Route::delete('/clients/{client}/contracts/{contract}/payments/{payment}', [App\Http\Controllers\Api\V1\ContractController::class, 'deactivatePaymentForClient'])->middleware('permission:62');
        Route::get('/providers/{provider}/contracts', [App\Http\Controllers\Api\V1\ContractController::class, 'indexForProvider'])->middleware('permission:14');
        Route::get('/providers/{provider}/contract-form-options', [App\Http\Controllers\Api\V1\ContractController::class, 'formOptionsForProvider'])->middleware('permission:14');
        Route::post('/providers/{provider}/contracts', [App\Http\Controllers\Api\V1\ContractController::class, 'storeForProvider'])->middleware('permission:14');
        Route::put('/providers/{provider}/contracts/{contract}', [App\Http\Controllers\Api\V1\ContractController::class, 'updateForProvider'])->middleware('permission:14');
        Route::delete('/providers/{provider}/contracts/{contract}', [App\Http\Controllers\Api\V1\ContractController::class, 'deactivateForProvider'])->middleware('permission:14');
        Route::get('/providers/{provider}/contracts/{contract}/attachments', [App\Http\Controllers\Api\V1\ContractController::class, 'attachmentsForProvider'])->middleware('permission:14');
        Route::post('/providers/{provider}/contracts/{contract}/attachments', [App\Http\Controllers\Api\V1\ContractController::class, 'storeAttachmentForProvider'])->middleware('permission:14');
        Route::get('/providers/{provider}/contracts/{contract}/attachments/{attachment}/download', [App\Http\Controllers\Api\V1\ContractController::class, 'downloadAttachmentForProvider'])->middleware('permission:14');
        Route::get('/providers/{provider}/contracts/{contract}/payments', [App\Http\Controllers\Api\V1\ContractController::class, 'paymentsForProvider'])->middleware('permission:14');
        Route::post('/providers/{provider}/contracts/{contract}/payments', [App\Http\Controllers\Api\V1\ContractController::class, 'storePaymentForProvider'])->middleware('permission:14');
        Route::delete('/providers/{provider}/contracts/{contract}/payments/{payment}', [App\Http\Controllers\Api\V1\ContractController::class, 'deactivatePaymentForProvider'])->middleware('permission:14');
        Route::get('/projects', [App\Http\Controllers\Api\V1\ProjectController::class, 'index'])->middleware('permission:88');
        Route::put('/projects/{project}', [App\Http\Controllers\Api\V1\ProjectController::class, 'update'])->middleware('permission:88');
        Route::get('/projects/{project}/activities', [App\Http\Controllers\Api\V1\ProjectController::class, 'activities'])->middleware('permission:88');
        Route::post('/projects/{project}/activities', [App\Http\Controllers\Api\V1\ProjectController::class, 'storeActivity'])->middleware('permission:88');
        Route::get('/projects/{project}/risks', [App\Http\Controllers\Api\V1\ProjectController::class, 'risks'])->middleware('permission:88');
        Route::post('/projects/{project}/risks', [App\Http\Controllers\Api\V1\ProjectController::class, 'storeRisk'])->middleware('permission:88');
    });
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
