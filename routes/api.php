<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class)->middleware('role:admin');
    Route::apiResource('products', UserController::class)->middleware('role:kantin,bc');

    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{id}', [TransactionController::class, 'show']);
    Route::get('transactions', [TransactionController::class, 'store']);

    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/daily', [ReportController::class, 'daily']);
    Route::get('reports/user/{id}', [ReportController::class, 'user']);
    Route::get('reports/me', [ReportController::class, 'me']);

    Route::get('blance', [BalanceController::class, 'mine']);
    Route::get('blance/{id}', [BalanceController::class, 'show'])->middleware('role:admin');
    Route::get('blance/topup', [BalanceController::class, 'topup'])->middleware('role:bank');
    Route::get('blance/withdraw', [BalanceController::class, 'withdraw'])->middleware('role:bank');
    
});



