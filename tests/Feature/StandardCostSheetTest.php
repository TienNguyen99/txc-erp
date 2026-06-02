<?php

namespace Tests\Feature;

use App\Models\DanhMucHangHoa;
use App\Models\StandardCostSheet;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Services\StandardCostSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StandardCostSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_build_and_activate_standard_cost_sheet(): void
    {
        $user = $this->warehouseManager();
        $product = DanhMucHangHoa::create(['ma_hh' => 'SP-001', 'ten_hh' => 'Thanh pham', 'don_vi' => 'PCS']);
        $material = DanhMucHangHoa::create(['ma_hh' => 'NVL-001', 'ten_hh' => 'Nguyen lieu', 'don_vi' => 'KG', 'gia_nvl' => 100]);

        $this->actingAs($user)
            ->post(route('admin.standard-cost-sheets.store'), [
                'product_id' => $product->id,
                'version' => '20260602-V1',
                'effective_date' => '2026-06-02',
                'standard_output_qty' => 1000,
                'sale_price_vnd' => 1000,
                'target_margin_pct' => 30,
                'vat_pct' => 10,
                'price_rounding_vnd' => 10,
            ])
            ->assertRedirect();

        $sheet = StandardCostSheet::firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.standard-cost-sheets.lines.store', $sheet), [
                'category' => 'material',
                'item_id' => $material->id,
                'code' => 'NVL-001',
                'name' => 'Nguyen lieu',
                'unit' => 'KG',
                'quantity' => 2,
                'waste_pct' => 10,
                'unit_price_vnd' => 100,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('admin.standard-cost-sheets.lines.store', $sheet), [
                'category' => 'labor',
                'name' => 'Nhan cong cat',
                'quantity' => 1,
                'unit_price_vnd' => 500,
                'allocation_qty' => 100,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->put(route('admin.standard-cost-sheets.update', $sheet), [
                'version' => '20260602-V1',
                'effective_date' => '2026-06-02',
                'standard_output_qty' => 1000,
                'sale_price_vnd' => 1000,
                'bank_interest_pct' => 1,
                'bank_interest_basis' => 'production_cost',
                'commission_pct' => 10,
                'commission_basis' => 'sale_price',
                'management_pct' => 25,
                'management_basis' => 'production_cost',
                'transport_cost_vnd' => 10,
            ])
            ->assertRedirect();

        $calculation = app(StandardCostSheetService::class)->calculate($sheet->fresh());
        $this->assertSame(225.0, $calculation['production_cost_vnd']);
        $this->assertSame(393.5, $calculation['total_cost_vnd']);
        $this->assertSame(606.5, $calculation['profit_vnd']);
        $this->assertSame(330.0, $calculation['break_even_price_vnd']);
        $this->assertSame(490.0, $calculation['suggested_price_vnd']);
        $this->assertSame(540.0, $calculation['quote_price_vnd']);

        $this->actingAs($user)
            ->post(route('admin.standard-cost-sheets.activate', $sheet))
            ->assertRedirect();

        $this->assertSame('active', $sheet->fresh()->status);
        $this->actingAs($user)
            ->get(route('admin.standard-cost-sheets.show', $sheet))
            ->assertOk()
            ->assertSee('Bảng tính giá vốn')
            ->assertSee('393.50');
    }

    public function test_manager_can_quick_create_material_from_cost_sheet(): void
    {
        $user = $this->warehouseManager();
        $gram = UnitOfMeasure::where('code', 'G')->firstOrFail();
        $kilogram = UnitOfMeasure::where('code', 'KG')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('admin.standard-cost-sheets.quick-create-item'), [
                'ma_hh' => ' SOI - POLY - 75 ',
                'ten_hh' => 'Soi poly 75',
                'nhom_hh' => 'Nguyen vat lieu',
                'base_uom_id' => $gram->id,
                'purchase_uom_id' => $kilogram->id,
                'purchase_to_base_factor' => 1000,
                'gia_nvl' => 80000,
            ])
            ->assertCreated()
            ->assertJsonPath('item.ma_hh', 'SOI-POLY-75')
            ->assertJsonPath('item.gia_nvl', 80000)
            ->assertJsonPath('item.don_vi', 'G')
            ->assertJsonPath('item.base_unit_cost_vnd', 80);

        $this->assertDatabaseHas('danh_muc_hang_hoa', [
            'ma_hh' => 'SOI-POLY-75',
            'ten_hh' => 'Soi poly 75',
            'active' => true,
        ]);
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
