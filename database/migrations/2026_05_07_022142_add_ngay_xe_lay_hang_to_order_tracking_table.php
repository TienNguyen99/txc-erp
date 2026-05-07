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
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->date('ngay_xe_lay_hang')->nullable()->after('cong_doan')->comment('Ngày xe đến lấy hàng giao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn('ngay_xe_lay_hang');
        });
    }
};
