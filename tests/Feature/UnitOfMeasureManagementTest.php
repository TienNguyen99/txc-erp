<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UnitOfMeasureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_manager_can_add_unit_of_measure(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate(['name' => 'catalog.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.units-of-measure.index'))
            ->assertOk()
            ->assertSee('Danh sách đơn vị tính');

        $this->actingAs($user)
            ->post(route('admin.units-of-measure.store'), [
                'code' => ' box ',
                'name' => 'Hộp',
                'dimension' => 'packaging',
                'factor_to_base' => 1,
                'active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('units_of_measure', ['code' => 'BOX', 'name' => 'Hộp', 'active' => true]);

        $box = UnitOfMeasure::where('code', 'BOX')->firstOrFail();
        $this->actingAs($user)
            ->put(route('admin.units-of-measure.update', $box), [
                'code' => 'BOX',
                'name' => 'Hộp nhỏ',
                'dimension' => 'packaging',
                'factor_to_base' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('units_of_measure', ['code' => 'BOX', 'name' => 'Hộp nhỏ', 'active' => false]);
    }
}
