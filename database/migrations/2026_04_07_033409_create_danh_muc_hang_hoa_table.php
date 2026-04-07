<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->id();
            $table->string('ma_hh')->unique();           // Mã hàng hóa
            $table->string('ten_hh');                      // Tên hàng hóa
            $table->string('nhom_hh')->nullable();         // Nhóm (VD: Vải, Phụ kiện, Bán thành phẩm)
            $table->string('don_vi')->nullable();          // Đơn vị tính (yard, mét, cái...)
            $table->decimal('don_gia', 12, 4)->default(0); // Đơn giá
            $table->string('hinh_anh')->nullable();        // Đường dẫn ảnh
            $table->text('mo_ta')->nullable();             // Mô tả
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_hang_hoa');
    }
};
