<?php

namespace Tests\Feature;

use App\Models\DanhMucHangHoa;
use App\Models\ProductionReceipt;
use App\Models\ProductionReport;
use App\Models\User;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductionReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_approve_reports_and_create_printable_receipt(): void
    {
        $user = $this->productionManager();

        DanhMucHangHoa::create([
            'ma_hh' => 'F2715CA007GFX',
            'ten_hh' => 'NHAN DET',
            'don_vi' => 'PCS',
        ]);

        $reportA = ProductionReport::create([
            'cong_doan' => 'Dệt',
            'ngay_sx' => '2026-05-16',
            'ca' => '1',
            'ma_nv' => 'NV001',
            'lenh_sx' => 'M-01462/26',
            'size' => 'F2715CA007GFX',
            'sl_dat' => 30,
            'sl_hu' => 0,
            'status' => 'pending',
        ]);

        $reportB = ProductionReport::create([
            'cong_doan' => 'Dệt',
            'ngay_sx' => '2026-05-16',
            'ca' => '1',
            'ma_nv' => 'NV002',
            'lenh_sx' => 'M-01432/26',
            'size' => 'F2715CA007GFX',
            'sl_dat' => 35,
            'sl_hu' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('admin.production-reports.approve-selected'), [
                'report_ids' => [$reportA->id, $reportB->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $reportA->fresh()->status);
        $this->assertSame('approved', $reportB->fresh()->status);

        $response = $this->actingAs($user)
            ->post(route('admin.production-reports.push-warehouse'), [
                'report_ids' => [$reportA->id, $reportB->id],
            ]);

        $receipt = ProductionReceipt::with('items')->first();
        $response->assertRedirect(route('admin.production-receipts.show', $receipt));

        $this->assertNotNull($receipt);
        $this->assertSame(2, $receipt->items->count());
        $this->assertSame('posted', $reportA->fresh()->status);
        $this->assertSame('posted', $reportB->fresh()->status);
        $this->assertSame(2, WarehouseTransaction::where('cong_doan', 'NHAPKHO')->count());
        $this->assertSame(60.0, (float) WarehouseTransaction::where('cong_doan', 'NHAPKHO')->sum('so_luong'));

        $this->actingAs($user)
            ->get(route('admin.production-receipts.print', $receipt))
            ->assertOk()
            ->assertSee('PHIẾU NHẬP KHO')
            ->assertSee('F2715CA007GFX');
    }

    public function test_posted_report_cannot_be_used_twice(): void
    {
        $user = $this->productionManager();

        $report = ProductionReport::create([
            'cong_doan' => 'Dệt',
            'ngay_sx' => now()->toDateString(),
            'lenh_sx' => 'M-00001/26',
            'size' => 'HH-001',
            'sl_dat' => 10,
            'sl_hu' => 0,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->post(route('admin.production-reports.push-warehouse'), [
                'report_ids' => [$report->id],
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->from(route('admin.production-reports.index'))
            ->post(route('admin.production-reports.push-warehouse'), [
                'report_ids' => [$report->id],
            ])
            ->assertRedirect(route('admin.production-reports.index'))
            ->assertSessionHasErrors('report_ids');
    }

    private function productionManager(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach (['production.view', 'production.edit'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $role->givePermissionTo(['production.view', 'production.edit']);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
