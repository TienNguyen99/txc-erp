<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OrderApiController;
use App\Http\Controllers\Api\OrderSyncController;
use App\Http\Controllers\API\WarehouseDashboardSyncController;

Route::middleware('auth')->group(function () {
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('orders', [OrderApiController::class, 'index']);
        Route::get('orders/{order}', [OrderApiController::class, 'show']);
    });
    Route::post('orders', [OrderApiController::class, 'store'])->middleware('permission:orders.create');
    Route::match(['put', 'patch'], 'orders/{order}', [OrderApiController::class, 'update'])->middleware('permission:orders.edit');
    Route::delete('orders/{order}', [OrderApiController::class, 'destroy'])->middleware('permission:orders.delete');

    Route::middleware('role:admin')->apiResource('users', App\Http\Controllers\API\UserApiController::class);

    Route::middleware('permission:warehouse.view')->group(function () {
        Route::get('warehouse-transactions', [App\Http\Controllers\API\WarehouseTransactionApiController::class, 'index']);
        Route::get('warehouse-transactions/{warehouse_transaction}', [App\Http\Controllers\API\WarehouseTransactionApiController::class, 'show']);
    });
    Route::post('warehouse-transactions', [App\Http\Controllers\API\WarehouseTransactionApiController::class, 'store'])->middleware('permission:warehouse.create');
    Route::match(['put', 'patch'], 'warehouse-transactions/{warehouse_transaction}', [App\Http\Controllers\API\WarehouseTransactionApiController::class, 'update'])->middleware('permission:warehouse.edit');
    Route::delete('warehouse-transactions/{warehouse_transaction}', [App\Http\Controllers\API\WarehouseTransactionApiController::class, 'destroy'])->middleware('permission:warehouse.delete');

    Route::middleware('permission:catalog.view')->group(function () {
        Route::get('danh-muc-hang-hoa', [App\Http\Controllers\API\DanhMucHangHoaApiController::class, 'index']);
        Route::get('danh-muc-hang-hoa/{danh_muc_hang_hoa}', [App\Http\Controllers\API\DanhMucHangHoaApiController::class, 'show']);
        Route::get('danh-muc-khach-hang', [App\Http\Controllers\API\DanhMucKhachHangApiController::class, 'index']);
        Route::get('danh-muc-khach-hang/{danh_muc_khach_hang}', [App\Http\Controllers\API\DanhMucKhachHangApiController::class, 'show']);
    });
    Route::post('danh-muc-hang-hoa', [App\Http\Controllers\API\DanhMucHangHoaApiController::class, 'store'])->middleware('permission:catalog.create');
    Route::match(['put', 'patch'], 'danh-muc-hang-hoa/{danh_muc_hang_hoa}', [App\Http\Controllers\API\DanhMucHangHoaApiController::class, 'update'])->middleware('permission:catalog.edit');
    Route::delete('danh-muc-hang-hoa/{danh_muc_hang_hoa}', [App\Http\Controllers\API\DanhMucHangHoaApiController::class, 'destroy'])->middleware('permission:catalog.delete');
    Route::post('danh-muc-khach-hang', [App\Http\Controllers\API\DanhMucKhachHangApiController::class, 'store'])->middleware('permission:catalog.create');
    Route::match(['put', 'patch'], 'danh-muc-khach-hang/{danh_muc_khach_hang}', [App\Http\Controllers\API\DanhMucKhachHangApiController::class, 'update'])->middleware('permission:catalog.edit');
    Route::delete('danh-muc-khach-hang/{danh_muc_khach_hang}', [App\Http\Controllers\API\DanhMucKhachHangApiController::class, 'destroy'])->middleware('permission:catalog.delete');
});

// ── Google Sheets Sync (no CSRF, token-based) ──
Route::prefix('orders')->group(function () {
    Route::post('sync', [OrderSyncController::class, 'sync']);
    Route::get('sync/status', [OrderSyncController::class, 'status']);
});

Route::get('warehouse-dashboard/sync', [WarehouseDashboardSyncController::class, 'show']);
