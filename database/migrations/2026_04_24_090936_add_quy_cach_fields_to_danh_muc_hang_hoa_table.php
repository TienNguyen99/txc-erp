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
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->string('quy_cach')->nullable()->after('don_gia')->comment('VD: Quấn cuộn, Xả thùng');
            $table->decimal('yards_per_roll', 8, 2)->nullable()->after('quy_cach')->comment('Số yard 1 cuộn');
            $table->integer('rolls_per_carton')->nullable()->after('yards_per_roll')->comment('Số cuộn 1 thùng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->dropColumn(['quy_cach', 'yards_per_roll', 'rolls_per_carton']);
        });
    }
};
