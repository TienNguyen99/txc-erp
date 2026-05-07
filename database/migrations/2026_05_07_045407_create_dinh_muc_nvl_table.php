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
        Schema::create('dinh_muc_nvl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('san_pham_id'); // Thành phẩm
            $table->unsignedBigInteger('nguyen_lieu_id'); // Nguyên liệu
            $table->decimal('so_luong', 10, 4)->default(1); // Định mức
            $table->decimal('ti_le_hao_hut', 5, 2)->default(0); // % Hao hụt
            $table->string('cong_doan')->nullable(); // Gắn với công đoạn nào
            $table->string('ghi_chu')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('san_pham_id')->references('id')->on('danh_muc_hang_hoa')->onDelete('cascade');
            $table->foreign('nguyen_lieu_id')->references('id')->on('danh_muc_hang_hoa')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate components for the same product in the same stage
            $table->unique(['san_pham_id', 'nguyen_lieu_id', 'cong_doan'], 'dinh_muc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dinh_muc_nvl');
    }
};
