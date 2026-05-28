<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_notifications', function (Blueprint $table) {
            $table->string('category', 50)->default('system')->after('type');
            $table->string('status', 30)->default('open')->after('is_read');
            $table->timestamp('resolved_at')->nullable()->after('status');
        });

        DB::table('erp_notifications')
            ->where('title', 'like', 'Tồn kho thấp:%')
            ->update(['category' => 'warehouse']);
    }

    public function down(): void
    {
        Schema::table('erp_notifications', function (Blueprint $table) {
            $table->dropColumn(['category', 'status', 'resolved_at']);
        });
    }
};
