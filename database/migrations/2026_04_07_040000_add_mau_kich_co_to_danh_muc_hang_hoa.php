<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->string('mau')->nullable()->after('ten_hh');
            $table->string('kich_co')->nullable()->after('mau');
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->dropColumn(['mau', 'kich_co']);
        });
    }
};
