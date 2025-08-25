<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ProductController;
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
    Route::get('products', [ProductController::class, 'index'])->middleware('role:canteen,bc,student,admin');
    Route::get('products/{id}', [ProductController::class, 'show'])->middleware('role:canteen,bc,student,admin');

    Route::post('products', [ProductController::class, 'store'])->middleware('role:canteen,bc');
    Route::put('products/{id}', [ProductController::class, 'update'])->middleware('role:canteen,bc');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->middleware('role:canteen,bc');

    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{id}', [TransactionController::class, 'show']);
    Route::post('transactions', [TransactionController::class, 'store']);

    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/daily', [ReportController::class, 'daily']);
    Route::get('reports/user/{id}', [ReportController::class, 'user']);
    Route::get('reports/me', [ReportController::class, 'me']);

    Route::get('balance', [BalanceController::class, 'mine']);
    Route::get('balance/{id}', [BalanceController::class, 'show'])->middleware('role:admin');
    Route::put('balance/topup', [BalanceController::class, 'topup'])->middleware('role:bank');
    Route::put('balance/withdraw', [BalanceController::class, 'withdraw'])->middleware('role:bank');
});
