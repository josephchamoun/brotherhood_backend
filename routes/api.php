<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;


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

    //Get all Roles
    Route::middleware('auth:sanctum')->get('/roles', [RoleController::class, 'index']);

    //Get all Sections
    Route::middleware('auth:sanctum')->get('/sections', [SectionController::class, 'index']);

    //Events
    Route::middleware('auth:sanctum')->delete('/event/delete/{id}', [EventController::class, 'destroy']);