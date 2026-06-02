<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderIndexPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_limits_rows_rendered_per_page(): void
    {
        $user = $this->adminWithOrderViewPermission();

        foreach (range(1, 120) as $number) {
            Order::create([
                'job_no' => sprintf('JOB-%06d', $number),
                'status' => 'pending',
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertViewHas('data', fn ($data) => $data->perPage() === 50 && $data->count() === 50);

        $this->actingAs($user)
            ->get(route('admin.orders.index', ['per_page' => 100]))
            ->assertOk()
            ->assertViewHas('data', fn ($data) => $data->perPage() === 100 && $data->count() === 100);

        $this->actingAs($user)
            ->get(route('admin.orders.index', ['per_page' => 1000]))
            ->assertOk()
            ->assertViewHas('data', fn ($data) => $data->perPage() === 50 && $data->count() === 50);
    }

    private function adminWithOrderViewPermission(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'orders.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
