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
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->decimal('price_usd', 15, 4)->nullable()->after('so_luong');
            $table->decimal('exchange_rate', 15, 2)->nullable()->after('price_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropColumn(['price_usd', 'exchange_rate']);
        });
    }
};
