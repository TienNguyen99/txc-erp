<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->date('receipt_date');
            $table->string('cong_doan')->nullable();
            $table->string('status', 50)->default('posted');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('production_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_receipt_id')->constrained('production_receipts')->cascadeOnDelete();
            $table->foreignId('production_report_id')->nullable()->constrained('production_reports')->nullOnDelete();
            $table->string('ten_san_pham')->nullable();
            $table->string('ma_hh');
            $table->string('mau')->nullable();
            $table->string('size')->nullable();
            $table->decimal('so_luong', 12, 2);
            $table->string('don_vi', 30)->default('PCS');
            $table->text('lenh_sx')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index(['ma_hh', 'mau', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_receipt_items');
        Schema::dropIfExists('production_receipts');
    }
};
