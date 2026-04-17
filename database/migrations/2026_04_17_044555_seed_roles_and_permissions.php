<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Migration này tạo toàn bộ Roles & Permissions cho hệ thống.
 * Dùng firstOrCreate → an toàn khi chạy lại (idempotent).
 *
 * ✅ Chạy tự động với: php artisan migrate
 * ✅ Không cần chạy seeder thủ công
 *
 * Khi cần thêm role/permission mới → tạo migration mới thay vì sửa file này.
 */
return new class extends Migration
{
    /** Modules và actions tương ứng */
    private array $modules = [
        'users'      => ['label' => 'Quản lý Users',       'actions' => ['view', 'create', 'edit', 'delete']],
        'orders'     => ['label' => 'Đơn hàng',            'actions' => ['view', 'create', 'edit', 'delete', 'import', 'export']],
        'tracking'   => ['label' => 'Order Tracking',      'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'warehouse'  => ['label' => 'Kho hàng',            'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'production' => ['label' => 'Báo cáo Sản xuất',   'actions' => ['view', 'create', 'edit', 'delete']],
        'lenh_sx'    => ['label' => 'Lệnh Sản xuất',       'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'catalog'    => ['label' => 'Danh mục (HH/KH)',    'actions' => ['view', 'create', 'edit', 'delete', 'import', 'export']],
    ];

    public function up(): void
    {
        // Reset cache để tránh stale data
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Tạo tất cả Permissions ──────────────────────────────────────
        $allPermissions = [];
        foreach ($this->modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $name = "{$module}.{$action}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $allPermissions[] = $name;
            }
        }

        // ── 2. Role: admin — toàn quyền ────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        // ── 3. Role: staff — chỉ xem một số module ─────────────────────────
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions(['warehouse.view', 'tracking.view', 'lenh_sx.view']);

        // ── 4. Role: manager — vào admin panel, cần admin cấp quyền riêng ──
        //    Mặc định không gán permissions → admin dùng trang Edit User để cấp
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        // Xóa toàn bộ roles & permissions khi rollback
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::whereIn('name', ['admin', 'staff', 'manager'])->delete();

        $permNames = [];
        foreach ($this->modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $permNames[] = "{$module}.{$action}";
            }
        }
        Permission::whereIn('name', $permNames)->delete();
    }
};
