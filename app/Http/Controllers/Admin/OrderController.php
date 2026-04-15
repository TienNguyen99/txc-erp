<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DanhMucKhachHang;
use App\Imports\OrderImport;
use App\Imports\CustomerOrderImport;
use App\Exports\OrderExport;
use App\Exports\OrderTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('bulk')) {
            $lines = preg_split('/[\r\n,;]+/', $request->bulk);
            $codes = array_values(array_filter(array_map('trim', $lines)));
            if ($codes) {
                $query->whereIn('fty_po', $codes);
            }
        } else {
            $query->when($request->search, fn($q, $s) => $q->where('job_no', 'like', "%$s%")->orWhere('fty_po', 'like', "%$s%"))
                  ->when($request->status, fn($q, $s) => $q->where('status', $s))
                  ->when($request->no_pl, fn($q) => $q->where(fn($q2) => $q2->whereNull('pl_number')->orWhere('pl_number', '')));
        }

        $data = $query->latest()->paginate(1000)->withQueryString();
        return view('admin.orders.index', compact('data'));
    }

    public function create()
    {
        $khachHangs = DanhMucKhachHang::where('active', true)->pluck('ten_kh', 'id');
        return view('admin.orders.form', compact('khachHangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'khach_hang_id' => 'nullable|exists:danh_muc_khach_hang,id',
            'job_no'        => 'required|string',
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'unit'          => 'nullable|string',
            'ma_hh'         => 'nullable|string',
            'yrd'           => 'nullable|numeric',
            'can_giao_1'    => 'nullable|numeric',
            'can_giao_2'    => 'nullable|numeric',
            'pl_number'     => 'nullable|string',
            'tagtime_etc'   => 'nullable|date',
            'sig_need_date' => 'nullable|date',
            'chart'         => 'nullable|string',
            'price_usd_auto'=> 'nullable|numeric',
            'price_usd'     => 'nullable|numeric',
            'to_khai'       => 'nullable|string',
            'lenh_sanxuat'  => 'nullable|string',
            'status'        => 'required|in:pending,in_production,done,shipped',
        ]);
        Order::create($validated);
        return redirect()->route('admin.orders.index')->with('success', 'Thêm đơn hàng thành công.');
    }

    public function edit(Order $order)
    {
        $khachHangs = DanhMucKhachHang::where('active', true)->pluck('ten_kh', 'id');
        return view('admin.orders.form', compact('order', 'khachHangs'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'khach_hang_id' => 'nullable|exists:danh_muc_khach_hang,id',
            'job_no'        => 'required|string',
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'unit'          => 'nullable|string',
            'ma_hh'         => 'nullable|string',
            'yrd'           => 'nullable|numeric',
            'can_giao_1'    => 'nullable|numeric',
            'can_giao_2'    => 'nullable|numeric',
            'pl_number'     => 'nullable|string',
            'tagtime_etc'   => 'nullable|date',
            'sig_need_date' => 'nullable|date',
            'chart'         => 'nullable|string',
            'price_usd_auto'=> 'nullable|numeric',
            'price_usd'     => 'nullable|numeric',
            'to_khai'       => 'nullable|string',
            'lenh_sanxuat'  => 'nullable|string',
            'status'        => 'required|in:pending,in_production,done,shipped',
        ]);
        $order->update($validated);
        return redirect()->route('admin.orders.index')->with('success', 'Cập nhật đơn hàng thành công.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Xóa đơn hàng thành công.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        try {
            Excel::import(new OrderImport, $request->file('file'));
            return redirect()->route('admin.orders.index')->with('success', 'Import đơn hàng thành công.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = collect($e->failures())->map(fn($f) => "Dòng {$f->row()}: {$f->attribute()} - " . implode(', ', $f->errors()));
            return redirect()->route('admin.orders.index')->with('error', 'Import lỗi validation: ' . $failures->take(5)->implode(' | '));
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')->with('error', 'Import lỗi: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new OrderExport, 'don-hang.xlsx');
    }

    public function template()
    {
        return Excel::download(new OrderTemplateExport, 'template-don-hang.xlsx');
    }

    public function importCustomer(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls|max:10240']);
        $import = new CustomerOrderImport;
        Excel::import($import, $request->file('file'));
        $msg = "Import thành công {$import->getImportedCount()} đơn hàng";
        if ($import->getSkippedCount() > 0) {
            $msg .= " (bỏ qua {$import->getSkippedCount()} dòng không hợp lệ)";
        }
        return redirect()->route('admin.orders.index')->with('success', $msg);
    }

    public function assignPlNumber(Request $request)
    {
        $request->validate([
            'order_ids'  => 'required|array|min:1',
            'order_ids.*'=> 'exists:orders,id',
            'pl_number'  => 'required|string|max:100',
        ]);

        $count = Order::whereIn('id', $request->order_ids)
                       ->update(['pl_number' => $request->pl_number]);

        return redirect()->back()->with('success', "Đã gán PL Number \"{$request->pl_number}\" cho {$count} đơn hàng.");
    }
}
