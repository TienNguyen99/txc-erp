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
        // orders -> khách hàng
        if (!Schema::hasColumn('orders', 'khach_hang_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('khach_hang_id')->nullable()->after('id');
                $table->foreign('khach_hang_id')->references('id')->on('danh_muc_khach_hang')->nullOnDelete();
            });
        }

        // warehouse_transactions -> hàng hóa (liên kết qua ma_hh)
        if (!Schema::hasColumn('warehouse_transactions', 'hang_hoa_id')) {
            Schema::table('warehouse_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('hang_hoa_id')->nullable()->after('ma_hh');
                $table->foreign('hang_hoa_id')->references('id')->on('danh_muc_hang_hoa')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['khach_hang_id']);
            $table->dropColumn('khach_hang_id');
        });
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropForeign(['hang_hoa_id']);
            $table->dropColumn('hang_hoa_id');
        });
    }
};
