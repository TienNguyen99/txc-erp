<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('da_tao_lenh_sx');
            $table->timestamp('invoice_issued_at')->nullable()->after('invoice_no');
            $table->decimal('invoice_exchange_rate', 12, 2)->nullable()->after('invoice_issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'invoice_issued_at', 'invoice_exchange_rate']);
        });
    }
};
