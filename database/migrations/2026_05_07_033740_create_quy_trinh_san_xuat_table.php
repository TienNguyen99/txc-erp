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
        Schema::create('quy_trinh_san_xuat', function (Blueprint $table) {
            $table->id();
            $table->string('ma_quy_trinh', 50)->unique();
            $table->string('ten_quy_trinh');
            $table->json('san_pham_ap_dung')->nullable()->comment('Lưu mảng mã hàng hóa hoặc nhóm hàng hóa');
            $table->date('ngay_hieu_luc')->nullable();
            $table->string('trang_thai', 50)->default('active')->comment('active, inactive');
            $table->json('flow_data')->nullable()->comment('Dữ liệu biểu đồ Drawflow');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quy_trinh_san_xuat');
    }
};
