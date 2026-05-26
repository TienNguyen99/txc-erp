<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('pl_number', 'orders_pl_number_index');
            $table->index('chart', 'orders_chart_index');
            $table->index('status', 'orders_status_index');
            $table->index('khach_hang_id', 'orders_khach_hang_id_index');
            $table->index('ma_hh', 'orders_ma_hh_index');
            $table->index('sig_need_date', 'orders_sig_need_date_index');
            $table->index(['job_no', 'ma_hh', 'color'], 'orders_import_key_index');
        });

        Schema::table('order_tracking', function (Blueprint $table) {
            $table->index('tracking_number_child', 'order_tracking_child_index');
            $table->index('pl_number', 'order_tracking_pl_number_index');
            $table->index('cong_doan', 'order_tracking_cong_doan_index');
            $table->index('ngay_xe_lay_hang', 'order_tracking_pickup_date_index');
            $table->index('invoice_issued_at', 'order_tracking_invoice_issued_at_index');
            $table->index(['tracking_number', 'pl_number'], 'order_tracking_lot_pl_index');
            $table->index(['tracking_number', 'size'], 'order_tracking_lot_size_index');
        });

        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->index(['ma_hh', 'cong_doan'], 'warehouse_transactions_ma_hh_stage_index');
            $table->index(['ngay', 'cong_doan'], 'warehouse_transactions_date_stage_index');
        });

        Schema::table('production_reports', function (Blueprint $table) {
            $table->index(['size', 'cong_doan'], 'production_reports_size_stage_index');
            $table->index('ngay_sx', 'production_reports_ngay_sx_index');
        });
    }

    public function down(): void
    {
        Schema::table('production_reports', function (Blueprint $table) {
            $table->dropIndex('production_reports_size_stage_index');
            $table->dropIndex('production_reports_ngay_sx_index');
        });

        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->dropIndex('warehouse_transactions_ma_hh_stage_index');
            $table->dropIndex('warehouse_transactions_date_stage_index');
        });

        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropIndex('order_tracking_child_index');
            $table->dropIndex('order_tracking_pl_number_index');
            $table->dropIndex('order_tracking_cong_doan_index');
            $table->dropIndex('order_tracking_pickup_date_index');
            $table->dropIndex('order_tracking_invoice_issued_at_index');
            $table->dropIndex('order_tracking_lot_pl_index');
            $table->dropIndex('order_tracking_lot_size_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_pl_number_index');
            $table->dropIndex('orders_chart_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_khach_hang_id_index');
            $table->dropIndex('orders_ma_hh_index');
            $table->dropIndex('orders_sig_need_date_index');
            $table->dropIndex('orders_import_key_index');
        });
    }
};
