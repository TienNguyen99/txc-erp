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
    Schema::create('order_tracking', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->string('pl_number')->nullable();
        $table->string('size')->nullable();
        $table->string('mau')->nullable();
        $table->string('kich')->nullable();          // KÍCH để sort
        $table->string('cong_doan')->nullable();
        $table->decimal('sl_don_hang', 10, 2)->default(0);
        $table->decimal('sl_san_xuat', 10, 2)->default(0);
        $table->timestamps();
        // balance, ty_le, ton → tính trong Controller, không lưu DB
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};
