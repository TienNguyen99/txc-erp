<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units_of_measure', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('dimension', 30);
            $table->decimal('factor_to_base', 18, 6)->default(1);
            $table->boolean('is_base')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('units_of_measure')->insert([
            ['code' => 'G', 'name' => 'Gam', 'dimension' => 'mass', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'KG', 'name' => 'Kilogram', 'dimension' => 'mass', 'factor_to_base' => 1000, 'is_base' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'PCS', 'name' => 'Cái', 'dimension' => 'quantity', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'M', 'name' => 'Mét', 'dimension' => 'length', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'CM', 'name' => 'Centimét', 'dimension' => 'length', 'factor_to_base' => 0.01, 'is_base' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'YARD', 'name' => 'Yard', 'dimension' => 'length', 'factor_to_base' => 0.9144, 'is_base' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ML', 'name' => 'Mililit', 'dimension' => 'volume', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'L', 'name' => 'Lít', 'dimension' => 'volume', 'factor_to_base' => 1000, 'is_base' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ROLL', 'name' => 'Cuộn', 'dimension' => 'packaging', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'CARTON', 'name' => 'Thùng', 'dimension' => 'packaging', 'factor_to_base' => 1, 'is_base' => true, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->foreignId('base_uom_id')->nullable()->after('don_vi')->constrained('units_of_measure')->nullOnDelete();
            $table->foreignId('purchase_uom_id')->nullable()->after('base_uom_id')->constrained('units_of_measure')->nullOnDelete();
            $table->decimal('purchase_to_base_factor', 18, 6)->default(1)->after('purchase_uom_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('base_uom_id')->nullable()->after('don_vi')->constrained('units_of_measure')->nullOnDelete();
            $table->foreignId('purchase_uom_id')->nullable()->after('base_uom_id')->constrained('units_of_measure')->nullOnDelete();
            $table->decimal('purchase_to_base_factor', 18, 6)->default(1)->after('purchase_uom_id');
        });

        $aliases = [
            'G' => ['g', 'gam', 'gram'],
            'KG' => ['kg', 'kilogram'],
            'PCS' => ['pcs', 'pc', 'cái', 'cai'],
            'M' => ['m', 'mét', 'met'],
            'CM' => ['cm'],
            'YARD' => ['yard', 'yd'],
            'ML' => ['ml'],
            'L' => ['l', 'lít', 'lit'],
            'ROLL' => ['roll', 'cuộn', 'cuon'],
            'CARTON' => ['carton', 'thùng', 'thung'],
        ];

        foreach ($aliases as $code => $values) {
            $unitId = DB::table('units_of_measure')->where('code', $code)->value('id');
            DB::table('danh_muc_hang_hoa')
                ->whereIn(DB::raw('LOWER(TRIM(don_vi))'), $values)
                ->update(['base_uom_id' => $unitId, 'purchase_uom_id' => $unitId]);
        }
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_uom_id');
            $table->dropConstrainedForeignId('base_uom_id');
            $table->dropColumn('purchase_to_base_factor');
        });

        Schema::table('danh_muc_hang_hoa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_uom_id');
            $table->dropConstrainedForeignId('base_uom_id');
            $table->dropColumn('purchase_to_base_factor');
        });

        Schema::dropIfExists('units_of_measure');
    }
};
