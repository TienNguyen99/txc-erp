<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DanhMucKhachHang;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $data = Order::when($request->search, fn($q, $s) => $q->where('job_no', 'like', "%$s%")->orWhere('fty_po', 'like', "%$s%"))
                     ->when($request->status, fn($q, $s) => $q->where('status', $s))
                     ->latest()->paginate(15)->withQueryString();
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
            'job_no'        => 'required|string|unique:orders,job_no',
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'qty'           => 'nullable|numeric',
            'unit'          => 'nullable|string',
            'size'          => 'nullable|string',
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
            'job_no'        => 'required|string|unique:orders,job_no,' . $order->id,
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'qty'           => 'nullable|numeric',
            'unit'          => 'nullable|string',
            'size'          => 'nullable|string',
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
}
