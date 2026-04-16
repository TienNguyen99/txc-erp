<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lenh_san_xuat', function (Blueprint $table) {
            $table->id();
            $table->string('lenh_so')->unique()->comment('Mã lệnh tổng, e.g. THUN-20260416-001');
            $table->string('chart')->comment('Tên Chart nguồn');
            $table->string('nhom_hh')->nullable()->comment('Nhóm hàng hóa chính');
            $table->float('pct_hao_hut')->default(10)->comment('% hao hụt mặc định');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });

        Schema::create('lenh_san_xuat_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lenh_san_xuat_id')->constrained('lenh_san_xuat')->cascadeOnDelete();
            $table->string('lenh_child')->comment('Mã lệnh con, e.g. THUN-20260416-001/1');
            $table->string('ma_hh');
            $table->string('ten_hh')->nullable();
            $table->string('mau')->nullable();
            $table->decimal('tong_yrd', 12, 2)->default(0)->comment('Tổng SL từ tất cả PO cùng Chart + ma_hh');
            $table->decimal('sl_can_sx', 12, 2)->default(0)->comment('SL cần SX = tong_yrd * (1 + hao_hut%)');
            $table->boolean('da_len_lenh')->default(false)->comment('Đã lên lệnh SX cho item này');
            $table->integer('stt')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lenh_san_xuat_items');
        Schema::dropIfExists('lenh_san_xuat');
    }
};
