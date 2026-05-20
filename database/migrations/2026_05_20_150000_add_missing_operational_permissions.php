<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'production.export',
            'tracking.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'admin')->first()?->givePermissionTo($permissions);

        $managerPermissions = Permission::query()
            ->whereNot('name', 'like', 'users.%')
            ->whereNot('name', 'like', '%.delete')
            ->pluck('name')
            ->all();

        Role::where('name', 'manager')->first()?->givePermissionTo($managerPermissions);

        Role::where('name', 'staff')->first()?->givePermissionTo([
            'warehouse.view',
            'warehouse.create',
            'tracking.view',
            'lenh_sx.view',
            'production.create',
        ]);
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::whereIn('name', [
            'production.export',
            'tracking.export',
        ])->delete();
    }
};
