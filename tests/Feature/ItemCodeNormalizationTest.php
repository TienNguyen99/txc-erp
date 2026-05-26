<?php

namespace Tests\Feature;

use App\Imports\DanhMucHangHoaImport;
use App\Models\DanhMucHangHoa;
use App\Models\Order;
use App\Support\ItemCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCodeNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_import_removes_spaces_from_item_code(): void
    {
        (new DanhMucHangHoaImport())->model([
            'ma_hh' => ' 70 mm ',
            'ten_hh' => 'Tape 70mm',
        ]);

        $this->assertDatabaseHas('danh_muc_hang_hoa', [
            'ma_hh' => '70mm',
            'ten_hh' => 'Tape 70mm',
        ]);
    }

    public function test_catalog_import_rejects_special_characters_in_item_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new DanhMucHangHoaImport())->model([
            'ma_hh' => '70/mm',
            'ten_hh' => 'Tape 70mm',
        ]);
    }

    public function test_models_normalize_item_code_spaces(): void
    {
        $item = DanhMucHangHoa::create([
            'ma_hh' => ' HH - 001 ',
            'ten_hh' => 'Hang hoa test',
        ]);

        $order = Order::create([
            'job_no' => 'JOB-001',
            'ma_hh' => ' 70 mm ',
            'status' => 'pending',
        ]);

        $this->assertSame('HH-001', $item->ma_hh);
        $this->assertSame('70mm', $order->ma_hh);
    }

    public function test_item_code_regex_rejects_special_characters(): void
    {
        $this->assertMatchesRegularExpression(ItemCode::VALIDATION_REGEX, 'HH-001_70mm');
        $this->assertDoesNotMatchRegularExpression(ItemCode::VALIDATION_REGEX, 'HH/001');
        $this->assertDoesNotMatchRegularExpression(ItemCode::VALIDATION_REGEX, 'HH#001');
    }
}
