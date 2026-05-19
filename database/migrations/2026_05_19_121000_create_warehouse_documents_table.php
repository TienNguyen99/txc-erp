<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_no')->unique();
            $table->enum('type', ['NHAPKHO', 'XUATKHO']);
            $table->date('document_date');
            $table->string('status', 50)->default('posted');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_document_id')->constrained('warehouse_documents')->cascadeOnDelete();
            $table->foreignId('warehouse_transaction_id')->nullable()->constrained('warehouse_transactions')->nullOnDelete();
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
        Schema::dropIfExists('warehouse_document_items');
        Schema::dropIfExists('warehouse_documents');
    }
};
