<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_reports', function (Blueprint $table) {
            $table->foreignId('approved_by_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');
            $table->foreignId('production_receipt_id')->nullable()->after('approved_at')->constrained('production_receipts')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->after('production_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::table('production_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_id');
            $table->dropConstrainedForeignId('production_receipt_id');
            $table->dropColumn(['approved_at', 'posted_at']);
        });
    }
};
