<?php

namespace Tests\Feature;

use App\Models\ErpNotification;
use App\Models\DanhMucHangHoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_are_grouped_like_tasks(): void
    {
        $user = $this->adminUser();

        ErpNotification::create([
            'type' => 'warning',
            'category' => ErpNotification::CATEGORY_DATA,
            'icon' => 'fa-list-check',
            'title' => 'Kiểm tra dữ liệu thiếu',
            'message' => '1 mã hàng hóa trong order chưa có định mức BOM.',
            'link' => '/admin/dinh-muc-nvl',
            'is_read' => false,
            'status' => ErpNotification::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Dữ liệu thiếu')
            ->assertSee('Cần xử lý')
            ->assertSee('Kiểm tra dữ liệu thiếu')
            ->assertSee('Xử lý xong');
    }

    public function test_notification_status_can_be_completed(): void
    {
        $user = $this->adminUser();
        $notification = ErpNotification::create([
            'type' => 'warning',
            'category' => ErpNotification::CATEGORY_DATA,
            'icon' => 'fa-dollar-sign',
            'title' => 'Thiếu đơn giá order: 1',
            'message' => '1 order chưa có price_usd hoặc price_usd_auto.',
            'is_read' => false,
            'status' => ErpNotification::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.notifications.status', $notification), [
                'status' => ErpNotification::STATUS_DONE,
            ])
            ->assertRedirect();

        $notification->refresh();
        $this->assertSame(ErpNotification::STATUS_DONE, $notification->status);
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->resolved_at);
    }

    public function test_catalog_missing_price_and_norm_create_actionable_notifications(): void
    {
        DanhMucHangHoa::create([
            'ma_hh' => 'THUN-MISS-001',
            'ten_hh' => 'Thun thiếu dữ liệu',
            'don_gia' => 0,
            'dinh_muc_thung' => null,
        ]);

        ErpNotification::syncOperationalChecks();

        $this->assertDatabaseHas('erp_notifications', [
            'title' => 'Danh mục thiếu đơn giá: 1',
            'category' => ErpNotification::CATEGORY_DATA,
            'status' => ErpNotification::STATUS_OPEN,
            'link' => '/admin/hang-hoa?missing=price',
        ]);
        $this->assertDatabaseHas('erp_notifications', [
            'title' => 'Danh mục thiếu định mức thùng: 1',
            'category' => ErpNotification::CATEGORY_DATA,
            'status' => ErpNotification::STATUS_OPEN,
            'link' => '/admin/hang-hoa?missing=carton_norm',
        ]);
    }

    private function adminUser(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
