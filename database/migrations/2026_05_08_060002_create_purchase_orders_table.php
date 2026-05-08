<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_po', 50)->unique();
            $table->unsignedBigInteger('nha_cung_cap_id')->nullable();
            $table->date('ngay_dat');
            $table->date('ngay_giao_du_kien')->nullable();
            $table->date('ngay_nhan_thuc_te')->nullable();
            $table->enum('trang_thai', ['draft', 'sent', 'confirmed', 'received', 'cancelled'])->default('draft');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('ma_hh', 100);
            $table->string('ten_hh')->nullable();
            $table->string('don_vi', 20)->nullable()->default('Yard');
            $table->decimal('so_luong', 15, 2)->default(0);
            $table->decimal('don_gia', 15, 4)->default(0);
            $table->decimal('da_nhan', 15, 2)->default(0);
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
