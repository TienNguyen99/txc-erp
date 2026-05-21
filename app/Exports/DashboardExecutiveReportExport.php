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
                ['Lot chưa giao', $report['undelivered_lot_count']],
                ['Lệnh gần hạn/trễ', $report['near_due_production']->count()],
                ['Mã thiếu NVL', $report['material_shortages']->count()],
                ['Công đoạn kẹt > 7 ngày', $report['stuck_stages']->sum('over_7_days')],
                ['PO/NVL trễ', $report['late_po_count']],
                ['Thiếu BOM/giá vốn', $report['missing_cost_data']->count()],
                ['Tỷ lệ lỗi', $ops['quality']['defect_rate_30d'] . '%'],
                ['Giá trị đơn hàng', $stats['order_revenue']],
                ['Đã xuất hóa đơn', $stats['invoiced_revenue']],
                ['Chưa hóa đơn', $stats['uninvoiced_revenue']],
                ['Tỷ lệ hóa đơn (%)', $stats['invoice_rate']],
            ]),
            new DashboardReportSheet('Lot chua giao', ['Lot', 'Khách hàng', 'Số mã', 'SL', 'Hạn', 'Công đoạn'], $report['undelivered_lots']->map(fn($r) => [
                $r['tracking_number'], $r['customer'], $r['total_items'], $r['total_qty'], $r['due_date'], $r['stage'],
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
            new DashboardReportSheet('Tai chinh', ['Khách hàng', 'Giá trị đơn', 'Đã hóa đơn', 'Chưa hóa đơn', 'Tỷ lệ hóa đơn %', 'Giá vốn', 'Margin', 'Margin %'], $ops['finance']['by_customer']->map(fn($r) => [
                $r['customer'], $r['revenue'], $r['invoiced_revenue'], $r['uninvoiced_revenue'], $r['invoice_rate'], $r['cost'], $r['margin'], $r['margin_rate'],
            ])->all()),
            new DashboardReportSheet('Mat hang doanh thu', ['Mã hàng', 'Tên hàng', 'SL', 'Số đơn', 'Doanh thu', 'Đã hóa đơn', 'Chưa hóa đơn', 'Margin %'], $ops['finance']['by_product']->map(fn($r) => [
                $r['ma_hh'], $r['ten_hh'], $r['qty'], $r['order_count'], $r['revenue'], $r['invoiced_revenue'], $r['uninvoiced_revenue'], $r['margin_rate'],
            ])->all()),
            new DashboardReportSheet('Du lieu thieu', ['Mã HH', 'Tên hàng', 'Thiếu'], $report['missing_cost_data']->map(fn($r) => [
                $r['ma_hh'], $r['ten_hh'], $r['issues'],
            ])->all()),
        ];
    }
}
