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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('job_no')->unique();
        $table->string('fty_po')->nullable();
        $table->string('im_number')->nullable();
        $table->string('color')->nullable();
        $table->decimal('qty', 10, 2)->nullable();
        $table->string('unit')->nullable();
        $table->string('size')->nullable();
        $table->decimal('yrd', 10, 2)->nullable();
        $table->decimal('can_giao_1', 10, 2)->nullable();  // CẦN GIAO 11/3
        $table->decimal('can_giao_2', 10, 2)->nullable();  // CẦN GIAO 18/3
        $table->string('pl_number')->nullable();
        $table->date('tagtime_etc')->nullable();
        $table->date('sig_need_date')->nullable();
        $table->string('chart')->nullable();
        $table->decimal('price_usd_auto', 10, 4)->nullable();
        $table->decimal('price_usd', 10, 4)->nullable();
        $table->string('to_khai')->nullable();
        $table->enum('status', ['pending','in_production','done','shipped'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
