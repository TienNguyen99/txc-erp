<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->boolean('da_tao_lenh_sx')->default(false)->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn('da_tao_lenh_sx');
        });
    }
};
