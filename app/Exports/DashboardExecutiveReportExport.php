<?php

namespace App\Exports;

use App\Services\DashboardMetricsService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExecutiveReportExport implements WithMultipleSheets
{
    private array $dashboard;

    public function __construct(private readonly array $filters)
    {
        $this->dashboard = app(DashboardMetricsService::class)->build($this->filters);
    }

    public function sheets(): array
    {
        $report = $this->dashboard['reportDashboard'];
        $ops = $this->dashboard['opsDashboard'];
        $stats = $this->dashboard['stats'];
        $filters = $this->dashboard['filters'];

        return [
            new DashboardReportSheet('Tong quan', ['Chỉ tiêu', 'Giá trị'], [
                ['Từ ngày', $filters['date_from']],
                ['Đến ngày', $filters['date_to']],
                ['Đơn đã giao', $report['order_status_counts']['shipped']],
                ['Đơn chưa giao', $report['order_status_counts']['pending'] + $report['order_status_counts']['in_production'] + $report['order_status_counts']['done']],
                ['Lệnh gần hạn/trễ', $report['near_due_production']->count()],
                ['Mã thiếu NVL', $report['material_shortages']->count()],
                ['Công đoạn kẹt > 7 ngày', $report['stuck_stages']->sum('over_7_days')],
                ['PO/NVL trễ', $report['late_po_count']],
                ['Thiếu BOM/giá vốn', $report['missing_cost_data']->count()],
                ['Tỷ lệ lỗi', $ops['quality']['defect_rate_30d'] . '%'],
                ['Doanh thu', $stats['total_revenue']],
            ]),
            new DashboardReportSheet('Don chua giao', ['Job No', 'Fty PO', 'Khách hàng', 'Mã HH', 'Hạn', 'Trạng thái', 'SL'], $report['undelivered_orders']->map(fn($r) => [
                $r['job_no'], $r['fty_po'], $r['customer'], $r['ma_hh'], $r['due_date'], $r['status'], $r['qty'],
            ])->all()),
            new DashboardReportSheet('Don da giao', ['Job No', 'Fty PO', 'Khách hàng', 'Mã HH', 'Hạn', 'SL'], $report['delivered_orders']->map(fn($r) => [
                $r['job_no'], $r['fty_po'], $r['customer'], $r['ma_hh'], $r['due_date'], $r['qty'],
            ])->all()),
            new DashboardReportSheet('Lenh gan han', ['Lệnh/Lot', 'Khách hàng', 'Công đoạn kẹt', 'Hạn', 'Còn/trễ ngày', 'Items'], $report['near_due_production']->map(fn($r) => [
                $r['tracking_number'], $r['customer'], $r['stage'], $r['due_date'], $r['days_left'], $r['total_items'],
            ])->all()),
            new DashboardReportSheet('Thieu NVL', ['Mã NVL', 'Tên NVL', 'Cần', 'Tồn', 'Thiếu'], $report['material_shortages']->map(fn($r) => [
                $r['ma_hh'], $r['ten_hh'], $r['required'], $r['on_hand'], $r['shortage'],
            ])->all()),
            new DashboardReportSheet('WIP cong doan', ['Công đoạn', 'WIP', 'Tuổi TB', '> 7 ngày'], $ops['wip']['aging']->map(fn($r) => [
                $r['stage'], $r['count'], $r['avg_days'], $r['over_7_days'],
            ])->all()),
            new DashboardReportSheet('PO tre', ['Số PO', 'Nhà cung cấp', 'Ngày đặt', 'Ngày giao DK', 'Trạng thái', 'Trễ ngày'], $report['late_purchase_orders']->map(fn($r) => [
                $r['so_po'], $r['supplier'], $r['ngay_dat'], $r['ngay_giao_du_kien'], $r['trang_thai'], $r['days_late'],
            ])->all()),
            new DashboardReportSheet('Tai chinh', ['Khách hàng', 'Doanh thu', 'Giá vốn', 'Margin', 'Margin %'], $ops['finance']['by_customer']->map(fn($r) => [
                $r['customer'], $r['revenue'], $r['cost'], $r['margin'], $r['margin_rate'],
            ])->all()),
            new DashboardReportSheet('Du lieu thieu', ['Mã HH', 'Tên hàng', 'Thiếu'], $report['missing_cost_data']->map(fn($r) => [
                $r['ma_hh'], $r['ten_hh'], $r['issues'],
            ])->all()),
        ];
    }
}
