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
    Schema::create('warehouse_transactions', function (Blueprint $table) {
        $table->id();
        $table->enum('cong_doan', ['NHAPKHO','XUATKHO']);
        $table->date('ngay');
        $table->string('size')->nullable();
        $table->string('mau')->nullable();
        $table->decimal('so_luong', 10, 2);
        $table->string('ma_nv')->nullable();
        $table->string('lenh_sx')->nullable();
        $table->text('note')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transactions');
    }
};
