<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nha_cung_cap', function (Blueprint $table) {
            $table->id();
            $table->string('ma_ncc', 50)->unique();
            $table->string('ten_ncc');
            $table->string('nguoi_lien_he')->nullable();
            $table->string('sdt', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('dia_chi')->nullable();
            $table->string('ma_so_thue', 50)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Thêm nha_cung_cap_id vào danh mục hàng hóa (NVL)
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->unsignedBigInteger('nha_cung_cap_id')->nullable()->after('id');
            $table->decimal('gia_nvl', 15, 4)->nullable()->after('nha_cung_cap_id')->comment('Giá NVL (VND/đơn vị)');
            $table->integer('ton_toi_thieu')->default(0)->after('gia_nvl')->comment('Tồn kho tối thiểu cảnh báo');
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->dropColumn(['nha_cung_cap_id', 'gia_nvl', 'ton_toi_thieu']);
        });
        Schema::dropIfExists('nha_cung_cap');
    }
};
