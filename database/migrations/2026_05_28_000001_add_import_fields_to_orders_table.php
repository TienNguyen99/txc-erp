<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'nhan_vien_id')) {
                $table->foreignId('nhan_vien_id')
                    ->nullable()
                    ->after('khach_hang_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'quy_cach')) {
                $table->string('quy_cach')->nullable()->after('ma_hh');
            }

            if (!Schema::hasColumn('orders', 'kich_co')) {
                $table->string('kich_co')->nullable()->after('ten_hh');
            }

            if (!Schema::hasColumn('orders', 'noi_giao')) {
                $table->string('noi_giao')->nullable()->after('sig_need_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'nhan_vien_id')) {
                $table->dropConstrainedForeignId('nhan_vien_id');
            }

            foreach (['quy_cach', 'kich_co', 'noi_giao'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
