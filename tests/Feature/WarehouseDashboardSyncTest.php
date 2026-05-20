<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseDashboardSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_dashboard_sync_requires_token(): void
    {
        Setting::create([
            'key' => 'api_sync_token',
            'value' => 'secret-token',
            'description' => 'API token',
            'type' => 'string',
        ]);

        $this->getJson('/api/warehouse-dashboard/sync?thang=5&nam=2026')
            ->assertUnauthorized();
    }

    public function test_warehouse_dashboard_sync_returns_sheet_ready_data(): void
    {
        Setting::create([
            'key' => 'api_sync_token',
            'value' => 'secret-token',
            'description' => 'API token',
            'type' => 'string',
        ]);

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh' => 'HH-001',
            'size' => 'M',
            'mau' => 'BLACK',
            'ngay' => '2026-04-30',
            'so_luong' => 10,
        ]);

        WarehouseTransaction::create([
            'cong_doan' => 'NHAPKHO',
            'ma_hh' => 'HH-001',
            'size' => 'M',
            'mau' => 'BLACK',
            'ngay' => '2026-05-02',
            'so_luong' => 25,
        ]);

        WarehouseTransaction::create([
            'cong_doan' => 'XUATKHO',
            'ma_hh' => 'HH-001',
            'size' => 'M',
            'mau' => 'BLACK',
            'ngay' => '2026-05-03',
            'so_luong' => 5,
        ]);

        Order::create([
            'job_no' => 'JOB-001',
            'ma_hh' => 'HH-001',
            'color' => 'BLACK',
            'yrd' => 40,
            'status' => 'pending',
        ]);

        $this->withToken('secret-token')
            ->getJson('/api/warehouse-dashboard/sync?thang=5&nam=2026')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('headers.0', 'Ma HH')
            ->assertJsonPath('headers.4', 'Nhap 02/05')
            ->assertJsonPath('headers.6', 'Xuat 03/05')
            ->assertJsonPath('rows.0.0', 'HH-001')
            ->assertJsonPath('rows.0.3', 10)
            ->assertJsonPath('rows.0.5', 25)
            ->assertJsonPath('rows.0.7', 5)
            ->assertJsonPath('rows.0.8', 30)
            ->assertJsonPath('rows.0.9', 40);
    }
}
