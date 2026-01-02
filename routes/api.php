<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ShopController;


    Route::apiResource('users', UserController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('shops', ShopController::class);




    Route::middleware('auth:sanctum')->post('/adduser', [UserController::class, 'store']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');


    //Show User by ID
    Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'show']);
