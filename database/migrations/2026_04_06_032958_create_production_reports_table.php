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
    Schema::create('production_reports', function (Blueprint $table) {
        $table->id();
        $table->string('cong_doan')->nullable();
        $table->date('ngay_sx');
        $table->string('ca')->nullable();            // Ca 1, 2, 3
        $table->string('ma_nv')->nullable();
        $table->string('lenh_sx')->nullable();
        $table->string('mau')->nullable();
        $table->string('size')->nullable();
        $table->decimal('dinh_muc', 10, 4)->nullable();
        $table->integer('so_band')->nullable();
        $table->decimal('ns_8h_1may', 10, 2)->nullable();
        $table->decimal('ns_gio_may', 10, 2)->nullable();
        $table->decimal('sl_dat', 10, 2)->nullable();
        $table->decimal('sl_hu', 10, 2)->nullable();
        // pct_hu = sl_hu/sl_dat*100 → tính trong Controller
        $table->integer('so_may')->nullable();
        $table->decimal('gio_sx', 5, 2)->nullable();
        $table->decimal('sl_yard_met', 10, 2)->nullable();
        $table->text('van_de')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_reports');
    }
};
