<?php

namespace Tests\Feature;

use App\Models\DanhMucHangHoa;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PurchaseOrderUnitConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_receiving_purchase_order_converts_purchase_quantity_to_inventory_unit(): void
    {
        $user = $this->warehouseManager();
        $gram = UnitOfMeasure::where('code', 'G')->firstOrFail();
        $kilogram = UnitOfMeasure::where('code', 'KG')->firstOrFail();
        DanhMucHangHoa::create([
            'ma_hh' => 'SOI-POLY-75',
            'ten_hh' => 'Soi poly 75',
            'don_vi' => 'G',
            'base_uom_id' => $gram->id,
            'purchase_uom_id' => $kilogram->id,
            'purchase_to_base_factor' => 1000,
            'gia_nvl' => 80000,
        ]);
        $po = PurchaseOrder::create([
            'so_po' => 'PO-UOM-001',
            'ngay_dat' => '2026-06-02',
            'trang_thai' => 'draft',
            'created_by' => $user->id,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'ma_hh' => 'SOI-POLY-75',
            'ten_hh' => 'Soi poly 75',
            'don_vi' => 'KG',
            'base_uom_id' => $gram->id,
            'purchase_uom_id' => $kilogram->id,
            'purchase_to_base_factor' => 1000,
            'so_luong' => 2,
            'don_gia' => 80000,
        ]);

        $this->actingAs($user)
            ->post(route('admin.purchase-orders.update-status', $po), ['trang_thai' => 'received'])
            ->assertRedirect();

        $transaction = WarehouseTransaction::where('ma_hh', 'SOI-POLY-75')->firstOrFail();
        $this->assertSame(2000.0, (float) $transaction->so_luong);
        $this->assertSame(80.0, (float) $transaction->price_usd);
    }

    private function warehouseManager(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        foreach (['warehouse.view', 'warehouse.edit'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $role->givePermissionTo(['warehouse.view', 'warehouse.edit']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
