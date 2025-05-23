<?php

use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\StatusController;
use App\Http\Controllers\api\v1\TaskController;
use App\Http\Controllers\api\v1\UserController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::prefix("v1")->group(function () {
    Route::prefix("auth")->group(function () {
        Route::post("register", [AuthController::class, "register"]);
        Route::post("login", [AuthController::class, "login"]);
    });

    Route::middleware('validate.token')->group(function () {
        Route::get("tasks/me", [TaskController::class, 'listTasks']);
        Route::get("tasks/statuses", [StatusController::class, 'listStatuses']);
        Route::apiResource('/tasks', TaskController::class);
    });
});
