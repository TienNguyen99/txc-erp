<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

/**
 * ⚠️  DEPRECATED — Không cần chạy seeder này nữa!
 *
 * Roles & Permissions được tạo tự động qua migration:
 *   2026_04_17_044555_seed_roles_and_permissions.php
 *
 * Chỉ giữ file này để migrate dữ liệu cũ (cột `role`) nếu cần.
 * Khi cần thêm role/permission mới → tạo migration MỚI.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Định nghĩa tất cả modules và các actions của chúng.
     * Dùng để tạo permissions dạng: "orders.view", "orders.create", v.v.
     */
    public static array $modules = [
        'users'      => ['label' => 'Quản lý Users',       'actions' => ['view', 'create', 'edit', 'delete']],
        'orders'     => ['label' => 'Đơn hàng',            'actions' => ['view', 'create', 'edit', 'delete', 'import', 'export']],
        'tracking'   => ['label' => 'Order Tracking',      'actions' => ['view', 'create', 'edit', 'delete']],
        'warehouse'  => ['label' => 'Kho hàng',            'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'production' => ['label' => 'Báo cáo Sản xuất',   'actions' => ['view', 'create', 'edit', 'delete']],
        'lenh_sx'    => ['label' => 'Lệnh Sản xuất',       'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'catalog'    => ['label' => 'Danh mục (HH/KH)',    'actions' => ['view', 'create', 'edit', 'delete', 'import', 'export']],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Tạo tất cả Permissions ─────────────────────────────────────────
        $allPermissions = [];
        foreach (self::$modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $name = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        // ── Tạo Roles & gán Permissions ─────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions); // admin có toàn bộ

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions(['warehouse.view', 'tracking.view', 'lenh_sx.view']);

        // Manager: vào được admin panel nhưng mặc định KHÔNG có permission nào
        // Admin phải cấp từng permission riêng qua trang Edit User → Tab "Quyền riêng"
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        // (không syncPermissions → mặc định rỗng)

        // ── Migrate dữ liệu cũ: cột `role` → Spatie role ──────────────────
        $migrated = 0;
        foreach (User::all() as $user) {
            // Chỉ gán role nếu chưa có role nào từ Spatie
            if ($user->roles->isEmpty()) {
                $oldRole = $user->role ?? 'staff';
                $user->syncRoles([in_array($oldRole, ['admin', 'staff']) ? $oldRole : 'staff']);
                $migrated++;
            }
        }

        $this->command->info('✅ Đã tạo ' . count($allPermissions) . ' permissions cho Spatie.');
        $this->command->info("✅ Đã migrate role cho {$migrated} user(s) chưa có role.");
    }
}
