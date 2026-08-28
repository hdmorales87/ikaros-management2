<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function checkUsername(Request $request): JsonResponse
    {
        return app(AuthController::class)->checkUsername($request);
    }

    public function login(Request $request): JsonResponse
    {
        return app(AuthController::class)->login($request);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        return app(AuthController::class)->updatePassword($request);
    }

    public function checkUserToken(Request $request): JsonResponse
    {
        return app(AuthController::class)->checkUserToken($request);
    }
}
