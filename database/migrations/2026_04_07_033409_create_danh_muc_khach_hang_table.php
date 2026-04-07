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
        Schema::create('danh_muc_khach_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ma_kh')->unique();             // Mã khách hàng
            $table->string('ten_kh');                       // Tên khách hàng / công ty
            $table->string('nguoi_lien_he')->nullable();    // Người liên hệ
            $table->string('sdt')->nullable();              // Số điện thoại
            $table->string('email')->nullable();
            $table->text('dia_chi')->nullable();            // Địa chỉ
            $table->string('ma_so_thue')->nullable();       // Mã số thuế
            $table->text('ghi_chu')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_khach_hang');
    }
};
