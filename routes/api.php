<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OrderApiController;

Route::apiResource('orders', OrderApiController::class);
Route::apiResource('users', App\Http\Controllers\API\UserApiController::class);
Route::apiResource('warehouse-transactions', App\Http\Controllers\API\WarehouseTransactionApiController::class);
Route::apiResource('danh-muc-hang-hoa', App\Http\Controllers\API\DanhMucHangHoaApiController::class);
Route::apiResource('danh-muc-khach-hang', App\Http\Controllers\API\DanhMucKhachHangApiController::class);
