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
        Schema::create('khach_hang_nhom_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khach_hang_id');
            $table->string('ma_nhom', 50);
            $table->string('ten_nhom', 255)->nullable();
            $table->timestamps();

            $table->foreign('khach_hang_id')->references('id')->on('danh_muc_khach_hang')->onDelete('cascade');
            $table->unique(['khach_hang_id', 'ma_nhom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khach_hang_nhom_hang');
    }
};
