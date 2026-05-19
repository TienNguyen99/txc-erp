<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_overheads', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('labor_cost_vnd', 18, 2)->default(0);
            $table->decimal('factory_overhead_vnd', 18, 2)->default(0);
            $table->decimal('other_cost_vnd', 18, 2)->default(0);
            $table->string('allocation_basis', 30)->default('output_qty');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_overheads');
    }
};
