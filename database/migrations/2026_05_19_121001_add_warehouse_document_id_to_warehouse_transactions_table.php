<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->foreignId('warehouse_document_id')->nullable()->after('production_receipt_id')->constrained('warehouse_documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_document_id');
        });
    }
};
