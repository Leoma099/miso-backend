<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BorrowNotificationController;
use App\Http\Controllers\DeliverRiderController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MyAccountController;

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

Route::middleware('auth:sanctum')->group(function ()
{
    Route::get('/brand', [BrandController::class, 'index']);
    Route::post('/brand', [BrandController::class, 'store']);
    Route::get('/brand/{id}', [BrandController::class, 'show']);
    Route::put('/brand/{id}', [BrandController::class, 'update']);
    Route::delete('/brand/{id}', [BrandController::class, 'destroy']);

    Route::get('/deliver-rider', [DeliverRiderController::class, 'index']);
    Route::post('/deliver-rider', [DeliverRiderController::class, 'store']);
    Route::get('/deliver-rider/{id}', [DeliverRiderController::class, 'show']);
    Route::put('/deliver-rider/{id}', [DeliverRiderController::class, 'update']);
    Route::delete('/deliver-rider/{id}', [DeliverRiderController::class, 'destroy']);

    Route::get('/department', [DepartmentController::class, 'index']);
    Route::post('/department', [DepartmentController::class, 'store']);
    Route::get('/department/{id}', [DepartmentController::class, 'show']);

    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::post('/calendarWalkin', [CalendarController::class, 'storeWalkin']);
    Route::get('/calendar/{id}', [CalendarController::class, 'show']);

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
    Route::post('/borrowWalkin', [BorrowController::class, 'storeWalkin']);
    Route::post('/borrow/client', [BorrowController::class, 'clientBorrows']);
    Route::put('/borrow/{id}', [BorrowController::class, 'update']);
    Route::delete('/borrow/{id}', [BorrowController::class, 'destroy']);
    Route::get('/status', [BorrowController::class, 'getReturnedBorrow']);
    Route::get('/borrow-statistics', [BorrowController::class, 'getBorrowStatistics']);
    Route::get('/borrowRecord', [BorrowController::class, 'getRecordBorrower']);
    Route::post('/borrowImport', [BorrowController::class, 'import']);
    Route::get('/borrowExport', [BorrowController::class, 'export']);
    Route::get('/borrowRecordExport', [BorrowController::class, 'borrowRecordExport']);
    Route::get('/borrowCountDepartment', [BorrowController::class, 'numberOfDepartmentBorrow']);
    Route::get('/borrowCountEquipment', [BorrowController::class, 'numberOfEquipmentBorrow']);
    Route::put('/borrow/{id}/return', [BorrowController::class, 'markAsReturned']);
    Route::get('/borrowPending', [BorrowController::class, 'getPendingBorrow']);

    Route::put('/borrowApprove/{id}', [BorrowController::class, 'approve']);
    Route::put('/borrowDecline/{id}', [BorrowController::class, 'decline']);
    Route::put('/borrowReturn/{id}', [BorrowController::class, 'returned']);

    Route::get('/account', [AccountController::class, 'index']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::post('/account', [AccountController::class, 'store']);
    Route::put('/account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);
    Route::get('/accountClient', [AccountController::class, 'clientDataInfo']);

    Route::put('/accountUpdate', [MyAccountController::class, 'update']);

    Route::get('/user', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

    Route::get('/notification', [UserController::class, 'index']);
    Route::get('/notification/{id}/read', [UserController::class, 'markAsRead']);
    Route::post('/notification/mark-all-read', [UserController::class, 'markAllAsRead']);
    Route::put('/notification/read', [UserController::class, 'unread']);

    Route::get('/borrow-notifications', [BorrowNotificationController::class, 'index']);
    Route::get('/borrow-notifications/unread/count', [BorrowNotificationController::class, 'unreadCount']);
    Route::get('/borrow-notification/{id}/read', [BorrowNotificationController::class, 'markAsRead']);
    Route::post('/mark-notifications-read', [BorrowNotificationController::class, 'markAllAsRead']);

    Route::post('/logout', [AuthController::class, 'logout']);

});

// Login route (fixed with name)
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::get('test/users', function()
{
    return \App\Models\Account::all();
});