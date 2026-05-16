<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_flow_order_tracking_production_warehouse_shipment(): void
    {
        $order = Order::create([
            'job_no' => 'JOB-' . uniqid(),
            'ma_hh' => 'HH-001',
            'color' => 'BLACK',
            'qty' => 100,
            'status' => 'pending',
        ]);

        OrderTracking::create([
            'order_id' => $order->id,
            'tracking_number' => 'OT-' . now()->format('Ymd') . '-001',
            'tracking_number_child' => 'OT-' . now()->format('Ymd') . '-001/1',
            'cong_doan' => 'Dệt',
            'sl_don_hang' => 100,
            'sl_san_xuat' => 60,
        ]);

        $order->updateStatusFromTracking();
        $order->refresh();
        $this->assertSame('in_production', $order->status);

        ProductionReport::create([
            'cong_doan' => 'Dệt',
            'ngay_sx' => now()->toDateString(),
            'ca' => '1',
            'ma_nv' => 'NV001',
            'lenh_sx' => 'LSX-001/1',
            'mau' => 'BLACK',
            'size' => 'HH-001',
            'sl_dat' => 60,
            'sl_hu' => 2,
            'status' => 'pending',
        ]);

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh' => 'HH-001',
            'ngay' => now()->toDateString(),
            'so_luong' => 60,
            'lenh_sx' => 'LSX-001/1',
        ]);

        WarehouseTransaction::create([
            'cong_doan' => 'XUATKHO',
            'ma_hh' => 'HH-001',
            'ngay' => now()->toDateString(),
            'so_luong' => 30,
            'lenh_sx' => 'SHIP-001',
        ]);

        $tonKho = WarehouseTransaction::where('ma_hh', 'HH-001')->nhapKho()->sum('so_luong')
            - WarehouseTransaction::where('ma_hh', 'HH-001')->xuatKho()->sum('so_luong');
        $this->assertEquals(30.0, (float) $tonKho);

        OrderTracking::where('order_id', $order->id)->update(['cong_doan' => 'Đã nhập kho']);
        $order->updateStatusFromTracking();
        $order->refresh();
        $this->assertSame('done', $order->status);

        OrderTracking::where('order_id', $order->id)->update(['cong_doan' => 'Đã giao']);
        $order->updateStatusFromTracking();
        $order->refresh();
        $this->assertSame('shipped', $order->status);
    }
}

