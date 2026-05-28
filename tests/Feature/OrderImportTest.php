<?php

namespace Tests\Feature;

use App\Exports\OtherCustomerOrderTemplateExport;
use App\Exports\OrderTemplateExport;
use App\Models\DanhMucKhachHang;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OrderImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_customer_template_uses_order_columns(): void
    {
        $expected = (new Order())->getFillable();

        $this->assertSame($expected, (new OrderTemplateExport())->headings());
        $this->assertSame($expected, (new OtherCustomerOrderTemplateExport())->headings());
    }

    public function test_other_customer_template_import_creates_order_customer_and_item(): void
    {
        $user = $this->adminWithPermissions(['orders.view', 'orders.import']);
        $customer = DanhMucKhachHang::create([
            'ma_kh' => 'PLPC',
            'ten_kh' => 'PLPC',
            'active' => true,
        ]);

        $file = $this->xlsxUpload([
            (new OtherCustomerOrderTemplateExport())->headings(),
            [
                '310613-AW25',
                $user->id,
                $customer->id,
                '9810030133',
                'Quan cuon',
                'DAY RAI SILICONE 2 DUONG',
                '30MM',
                'DKT-N07A BLACK',
                'MET',
                3980,
                '04/03/2025',
                '04/05/2025',
                'PLPC',
                'PB1-SAMDANG-31517',
                'PB1-SAMDANG-31517',
                '9810030133',
                3980,
                '',
                '',
                'PB1-SAMDANG-31517',
                '',
                '',
                '',
                '',
                'pending',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('admin.orders.import'), ['file' => $file])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success');

        $order = Order::where('job_no', 'PB1-SAMDANG-31517')->first();
        $this->assertNotNull($order);
        $this->assertSame($user->id, $order->nhan_vien_id);
        $this->assertSame($customer->id, $order->khach_hang_id);
        $this->assertSame('9810030133', $order->ma_hh);
        $this->assertSame('Quan cuon', $order->quy_cach);
        $this->assertSame('30MM', $order->kich_co);
        $this->assertSame('PLPC', $order->noi_giao);
        $this->assertSame('310613-AW25', $order->chart);
        $this->assertSame('2025-05-04', $order->sig_need_date->format('Y-m-d'));
        $this->assertNull($order->to_khai);
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

    private function xlsxUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 1], $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'orders-import-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'orders.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            UPLOAD_ERR_OK,
            true
        );
    }
}
