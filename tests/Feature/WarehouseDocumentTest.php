<?php

namespace Tests\Feature;

use App\Models\DanhMucHangHoa;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\User;
use App\Models\WarehouseDocument;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WarehouseDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_warehouse_transaction_creates_printable_document(): void
    {
        $user = $this->warehouseUser();

        DanhMucHangHoa::create([
            'ma_hh' => 'HH-001',
            'ten_hh' => 'NHAN DET',
            'don_vi' => 'PCS',
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.warehouse-transactions.store'), [
                'cong_doan' => 'NHAPKHO',
                'ma_hh' => 'HH-001',
                'ngay' => '2026-05-16',
                'mau' => 'BLACK',
                'size' => '70MM',
                'so_luong' => 120,
                'ma_nv' => 'NVKHO',
                'lenh_sx' => 'M-01462/26',
                'note' => 'Nhap thu cong',
            ]);

        $document = WarehouseDocument::with('items')->first();

        $response->assertRedirect(route('admin.warehouse-documents.show', $document));
        $this->assertSame('NHAPKHO', $document->type);
        $this->assertSame(1, $document->items->count());
        $this->assertSame(1, WarehouseTransaction::where('warehouse_document_id', $document->id)->count());

        $this->actingAs($user)
            ->get(route('admin.warehouse-documents.print', $document))
            ->assertOk()
            ->assertSee('PHIẾU NHẬP KHO')
            ->assertSee('HH-001');
    }

    public function test_can_create_document_from_existing_transactions(): void
    {
        $user = $this->warehouseUser();

        $first = WarehouseTransaction::create([
            'cong_doan' => 'XUATKHO',
            'ma_hh' => 'HH-002',
            'ngay' => '2026-05-16',
            'so_luong' => 10,
            'lenh_sx' => 'PX-001',
        ]);

        $second = WarehouseTransaction::create([
            'cong_doan' => 'XUATKHO',
            'ma_hh' => 'HH-003',
            'ngay' => '2026-05-16',
            'so_luong' => 20,
            'lenh_sx' => 'PX-001',
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.warehouse-documents.from-transactions'), [
                'transaction_ids' => [$first->id, $second->id],
            ]);

        $document = WarehouseDocument::with('items')->first();

        $response->assertRedirect(route('admin.warehouse-documents.show', $document));
        $this->assertSame('XUATKHO', $document->type);
        $this->assertSame(2, $document->items->count());
        $this->assertSame($document->id, $first->fresh()->warehouse_document_id);
        $this->assertSame($document->id, $second->fresh()->warehouse_document_id);

        $this->actingAs($user)
            ->get(route('admin.warehouse-documents.print', $document))
            ->assertOk()
            ->assertSee('PHIẾU XUẤT KHO')
            ->assertSee('HH-002');
    }

    public function test_shipped_lots_are_counted_but_not_shown_for_picking(): void
    {
        $user = $this->warehouseUser();

        $order = Order::create([
            'job_no' => 'JOB-SHIPPED-001',
            'pl_number' => 'PL-SHIPPED-001',
            'ma_hh' => 'HH-SHIPPED',
            'yrd' => 50,
            'status' => 'shipped',
        ]);

        OrderTracking::create([
            'order_id' => $order->id,
            'tracking_number' => 'OT-SHIPPED-001',
            'pl_number' => 'PL-SHIPPED-001',
            'size' => 'HH-SHIPPED',
            'cong_doan' => 'Chờ sản xuất',
            'sl_don_hang' => 50,
        ]);

        $this->actingAs($user)
            ->get(route('admin.warehouse-transactions.index'))
            ->assertOk()
            ->assertSee('Lot shipped: 1', false)
            ->assertSee('Thieu XUATKHO: 1', false)
            ->assertSee('Tổng: 0', false);

        $this->actingAs($user)
            ->post(route('admin.warehouse-transactions.sync-shipped-xuat-kho'))
            ->assertRedirect(route('admin.warehouse-transactions.index'));

        $this->assertDatabaseHas('warehouse_transactions', [
            'cong_doan' => 'XUATKHO',
            'ma_hh' => 'HH-SHIPPED',
            'so_luong' => 50,
        ]);
        $this->assertSame(OrderTracking::STAGE_XUAT_KHO, OrderTracking::first()->fresh()->cong_doan);
    }

    private function warehouseUser(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach (['warehouse.view', 'warehouse.create', 'warehouse.edit'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $role->givePermissionTo(['warehouse.view', 'warehouse.create', 'warehouse.edit']);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
