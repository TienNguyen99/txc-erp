<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [];
        foreach (RolePermissionSeeder::$modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $name = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(collect($allPermissions)
            ->reject(fn (string $permission) => str_starts_with($permission, 'users.') || str_ends_with($permission, '.delete'))
            ->values()
            ->all());

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'warehouse.view',
            'warehouse.create',
            'tracking.view',
            'lenh_sx.view',
            'production.create',
        ]);

        $this->defaultUser('Admin TXC', 'admin@txc.vn', 'admin');
        $this->defaultUser('Quan ly TXC', 'manager@txc.vn', 'manager');
        $this->defaultUser('Nhan vien kho', 'staff@txc.vn', 'staff');
    }

    private function defaultUser(string $name, string $email, string $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);
    }
}
