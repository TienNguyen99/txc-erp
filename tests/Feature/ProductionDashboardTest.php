<?php

namespace Tests\Feature;

use App\Models\ProductionReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_manager_can_view_focused_dashboard(): void
    {
        $user = $this->userWithPermissions(['production.view']);

        ProductionReport::create([
            'cong_doan' => 'Dệt',
            'ngay_sx' => now()->toDateString(),
            'ca' => '1',
            'lenh_sx' => 'LSX-DASHBOARD-001',
            'sl_dat' => 95,
            'sl_hu' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('admin.production-dashboard.index'))
            ->assertOk()
            ->assertSee('Dashboard Sản xuất')
            ->assertSee('Sản lượng đạt')
            ->assertSee('Tổng lệnh sản xuất')
            ->assertSee('LSX-DASHBOARD-001')
            ->assertSee('95')
            ->assertSee('5.00%');
    }

    public function test_user_without_production_permission_cannot_view_dashboard(): void
    {
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)
            ->get(route('admin.production-dashboard.index'))
            ->assertForbidden();
    }

    private function userWithPermissions(array $permissions): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
