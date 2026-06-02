<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('danh_muc_hang_hoa')->cascadeOnDelete();
            $table->string('version', 50);
            $table->date('effective_date');
            $table->string('status', 20)->default('draft');
            $table->decimal('standard_output_qty', 15, 4)->default(1);
            $table->decimal('sale_price_vnd', 18, 4)->default(0);
            $table->decimal('bank_interest_pct', 7, 4)->default(0);
            $table->string('bank_interest_basis', 30)->default('production_cost');
            $table->decimal('commission_pct', 7, 4)->default(0);
            $table->string('commission_basis', 30)->default('sale_price');
            $table->decimal('management_pct', 7, 4)->default(0);
            $table->string('management_basis', 30)->default('production_cost');
            $table->decimal('transport_cost_vnd', 18, 4)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'version']);
            $table->index(['product_id', 'status', 'effective_date'], 'standard_cost_sheet_lookup_index');
        });

        Schema::create('standard_cost_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_cost_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->foreignId('item_id')->nullable()->constrained('danh_muc_hang_hoa')->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('stage')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 18, 6)->default(1);
            $table->decimal('waste_pct', 7, 4)->default(0);
            $table->decimal('unit_price_vnd', 18, 4)->default(0);
            $table->decimal('allocation_qty', 18, 4)->nullable();
            $table->string('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['standard_cost_sheet_id', 'category'], 'standard_cost_line_group_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_cost_lines');
        Schema::dropIfExists('standard_cost_sheets');
    }
};
