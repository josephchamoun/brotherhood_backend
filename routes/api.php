<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ChabibaController;
use App\Http\Controllers\Tala2e3Controller;
use App\Http\Controllers\ForsanController;

    Route::apiResource('users', UserController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('shops', ShopController::class);




    Route::middleware('auth:sanctum')->post('/adduser', [UserController::class, 'store']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');


    //Users
    Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'show']);
    Route::middleware('auth:sanctum')->delete('/user/delete/{id}', [UserController::class, 'destroy']);

    Route::middleware('auth:sanctum')->get('/chabiba', [UserController::class, 'chabiba']);
    Route::middleware('auth:sanctum')->get('/tala2e3', [UserController::class, 'tala2e3']);
    Route::middleware('auth:sanctum')->get('/forsan', [UserController::class, 'forsan']);

    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/{user}/add-to-section', [UserController::class, 'addToSection']);
});

    //Get all Roles
    Route::middleware('auth:sanctum')->get('/roles', [RoleController::class, 'index']);

    //Get all Sections
    Route::middleware('auth:sanctum')->get('/sections', [SectionController::class, 'index']);

    //Events
    Route::middleware('auth:sanctum')->delete('/event/delete/{id}', [EventController::class, 'destroy']);

    Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chabiba-role', [ChabibaController::class, 'index']);
    Route::post('/chabiba/assign-role', [ChabibaController::class, 'assignRole']);
    Route::post('/chabiba/remove-role', [ChabibaController::class, 'removeRole']);

        Route::post('/chabiba/activate-user', [ChabibaController::class, 'activateUser']);
    Route::post('/chabiba/inactivate-user', [ChabibaController::class, 'inactivateUser']);
});
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tala2e3-role', [Tala2e3Controller::class, 'index']);
    Route::post('/tala2e3/assign-role', [Tala2e3Controller::class, 'assignRole']);
    Route::post('/tala2e3/remove-role', [Tala2e3Controller::class, 'removeRole']);
});
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/forsan-role', [ForsanController::class, 'index']);
    Route::post('/forsan/assign-role', [ForsanController::class, 'assignRole']);
    Route::post('/forsan/remove-role', [ForsanController::class, 'removeRole']);
});

});
