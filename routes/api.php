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
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MoneyboxController;
use App\Http\Controllers\DriveAccountController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\MetaController;

Route::get('/stats', [UserController::class, 'stats']);



    Route::middleware('auth:sanctum')->post('/adduser', [UserController::class, 'store']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');


    //Users
        Route::get('/usersmobile', [UserController::class, 'index']);
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
    Route::get('/eventsmobile', [EventController::class, 'index']);

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
    Route::post('/tala2e3/end-role', [Tala2e3Controller::class, 'endRole']);
        Route::post('/tala2e3/activate-user', [Tala2e3Controller::class, 'activateUser']);
    Route::post('/tala2e3/inactivate-user', [Tala2e3Controller::class, 'inactivateUser']);
});
    Route::middleware('auth:sanctum')->group(function () {
Route::get('/forsan-role', [ForsanController::class, 'index']);
    Route::post('/forsan/assign-role', [ForsanController::class, 'assignRole']);
    Route::post('/forsan/remove-role', [ForsanController::class, 'removeRole']);
    Route::post('/forsan/end-role', [ForsanController::class, 'endRole']);
        Route::post('/forsan/activate-user', [ForsanController::class, 'activateUser']);
    Route::post('/forsan/inactivate-user', [ForsanController::class, 'inactivateUser']);
});

});


// shops
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shops', [ShopController::class, 'index']);
    Route::post('/shops', [ShopController::class, 'store']);
   
    Route::delete('/shops/{id}', [ShopController::class, 'destroy']);


});

//contacts
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
});
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])
    ->middleware('auth:sanctum');


//Meetings
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/meetings', [MeetingController::class, 'index']);
    Route::post('/addmeetinglink', [MeetingController::class, 'store']);
});

//drive accounts
Route::middleware('auth:sanctum')->group(function () {

    // 🔹 Get all drive accounts
    Route::get('/drive-accounts', [DriveAccountController::class, 'index']);

    // 🔹 Create new drive account
    Route::post('/drive-accounts', [DriveAccountController::class, 'store']);

    // 🔹 Get single drive account (with plain password)
    Route::get('/drive-accounts/{id}', [DriveAccountController::class, 'show']);

    // 🔹 Update drive account
    Route::put('/drive-accounts/{id}', [DriveAccountController::class, 'update']);
    Route::patch('/drive-accounts/{id}', [DriveAccountController::class, 'update']);

    // 🔹 Delete drive account
    Route::delete('/drive-accounts/{id}', [DriveAccountController::class, 'destroy']);

});


Route::get('/clear-cache', function() {
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    
    return response()->json(['message' => 'Cache cleared successfully!']);
});

Route::middleware('auth:sanctum')->group(function () {
Route::get('/moneyboxes', [MoneyboxController::class, 'index']);
Route::patch('/moneyboxes/{id}', [MoneyboxController::class, 'update']);
});

//elections
Route::middleware('auth:sanctum')->group(function () {
Route::get('/elections', [ElectionController::class, 'index']);
Route::post('/elections', [ElectionController::class, 'store']);
Route::delete('/elections/{id}', [ElectionController::class, 'destroy']);
});



//(no auth needed, it's just timestamps)
Route::get('/meta', [MetaController::class, 'index']);