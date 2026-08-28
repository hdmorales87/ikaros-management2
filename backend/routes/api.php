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
    Route::get('/getPoliticasSeguridad', [App\Http\Controllers\CompanyController::class, 'getPoliticasSeguridad']);
    Route::get('/getIdioma', [App\Http\Controllers\CompanyController::class, 'getIdioma']);
});

// Protected routes (JWT authentication required)
Route::middleware(['security.headers', 'authenticate.jwt'])->group(function () {
    // Company routes
    Route::get('/getCompanyData', [App\Http\Controllers\CompanyController::class, 'getCompanyData']);
    Route::get('/getCompanyModules', [App\Http\Controllers\CompanyController::class, 'getCompanyModules']);
    
    // User routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('/users/{id}', [App\Http\Controllers\UserController::class, 'show']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
});
