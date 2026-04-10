<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->unsignedInteger('dinh_muc_thung')->nullable()->after('don_gia')->comment('Số yard/thùng (carton capacity)');
            $table->decimal('net_weight', 8, 2)->nullable()->after('dinh_muc_thung')->comment('Net weight per full carton (KGS)');
            $table->decimal('gross_weight', 8, 2)->nullable()->after('net_weight')->comment('Gross weight per full carton (KGS)');
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->dropColumn(['dinh_muc_thung', 'net_weight', 'gross_weight']);
        });
    }
};
