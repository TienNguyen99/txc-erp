<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderTrackingLotListTest extends TestCase
{
    use RefreshDatabase;

    public function test_lot_list_shows_pl_numbers_and_vat_status(): void
    {
        $user = $this->adminWithPermissions(['tracking.view', 'tracking.export']);

        $order = Order::create([
            'job_no' => 'JOB-PL-001',
            'pl_number' => 'PL-001',
            'ma_hh' => 'HH-001',
            'color' => 'BLACK',
            'yrd' => 100,
            'status' => 'pending',
        ]);

        OrderTracking::create([
            'order_id' => $order->id,
            'tracking_number' => 'OT-20260526-001',
            'pl_number' => 'PL-001',
            'size' => 'HH-001',
            'mau' => 'BLACK',
            'cong_doan' => 'Đã giao',
            'ngay_xe_lay_hang' => '2026-05-26',
            'sl_don_hang' => 100,
            'invoice_no' => 'INV-001',
            'invoice_issued_at' => '2026-05-26 08:00:00',
            'invoice_exchange_rate' => 25400,
        ]);

        $this->actingAs($user)
            ->get(route('admin.order-tracking.index'))
            ->assertOk()
            ->assertSee('OT-20260526-001')
            ->assertSee('PL-001')
            ->assertSee('Đã xuất');
    }

    private function adminWithPermissions(array $permissions): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
