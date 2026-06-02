<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_cost_sheets', function (Blueprint $table) {
            $table->decimal('target_margin_pct', 7, 4)->default(30)->after('sale_price_vnd');
            $table->decimal('vat_pct', 7, 4)->default(0)->after('target_margin_pct');
            $table->decimal('price_rounding_vnd', 18, 4)->default(1)->after('vat_pct');
        });
    }

    public function down(): void
    {
        Schema::table('standard_cost_sheets', function (Blueprint $table) {
            $table->dropColumn(['target_margin_pct', 'vat_pct', 'price_rounding_vnd']);
        });
    }
};
