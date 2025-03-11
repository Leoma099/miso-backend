<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/equipment', [EquipmentController::class, 'index']);
    Route::get('/equipment/{id}', [EquipmentController::class, 'show']);
    Route::post('/equipment', [EquipmentController::class, 'store']);
    Route::put('/equipment/{id}', [EquipmentController::class, 'update']);
    Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy']);
    Route::get('/equipment-availability', [EquipmentController::class, 'getAvailabilityStats']);
    Route::post('/import-equipment', [EquipmentController::class, 'import']);
    Route::get('/export-equipment', [EquipmentController::class, 'export']);

    Route::get('/borrow', [BorrowController::class, 'index']);
    Route::get('/borrow/{id}', [BorrowController::class, 'show']);
    Route::post('/borrow', [BorrowController::class, 'store']);
    Route::post('/borrow/client', [BorrowController::class, 'clientBorrows']);
    Route::put('/borrow/{id}', [BorrowController::class, 'update']);
    Route::delete('/borrow/{id}', [BorrowController::class, 'destroy']);
    Route::get('/borrow-statistics', [BorrowController::class, 'getBorrowStatistics']);


    Route::get('/account', [AccountController::class, 'index']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::post('/account', [AccountController::class, 'store']);
    Route::put('/account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);

    Route::get('/user', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

    Route::get('/notification', [UserController::class, 'index']);
    Route::get('/notification/{id}/read', [UserController::class, 'markAsRead']);
    Route::post('/notification/mark-all-read', [UserController::class, 'markAllAsRead']);
    Route::put('/notification/read', [UserController::class, 'unread']);

    Route::post('/logout', [AuthController::class, 'logout']);

});

// Login route (fixed with name)
Route::post('login', [AuthController::class, 'login'])->name('login');