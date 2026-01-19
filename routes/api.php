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
use App\Http\Controllers\ContactController;


Route::get('/stats', [UserController::class, 'stats']);



    Route::middleware('auth:sanctum')->post('/adduser', [UserController::class, 'store']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');


    //Users
        Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);
    Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'show']);
    Route::middleware('auth:sanctum')->delete('/user/delete/{id}', [UserController::class, 'destroy']);
    Route::get('/users/{id}/profile', [UserController::class, 'profile'])
    ->middleware('auth:sanctum');
    Route::put('/users/{id}', [UserController::class, 'updateProfile'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/myprofile', [UserController::class, 'myProfile']);
Route::middleware('auth:sanctum')->put('/myprofile', [UserController::class, 'updateMyProfile']);





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
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/addevent', [EventController::class, 'store']);
    Route::put('/events/{id}/details', [EventController::class, 'updateDetails']);
    Route::put('/events/{id}/financials', [EventController::class, 'updateFinancials']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
});

    Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chabiba-role', [ChabibaController::class, 'index']);
    Route::post('/chabiba/assign-role', [ChabibaController::class, 'assignRole']);
    Route::post('/chabiba/remove-role', [ChabibaController::class, 'removeRole']);
    Route::post('/chabiba/end-role', [ChabibaController::class, 'endRole']);
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


// shops
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shops', [ShopController::class, 'index']);
    Route::post('/shops', [ShopController::class, 'store']);
});

//contacts
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
});

