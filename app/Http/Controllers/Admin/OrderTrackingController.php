<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        $data = OrderTracking::with('order')
                    ->when($request->search, fn($q, $s) => $q->where('pl_number', 'like', "%$s%")->orWhere('mau', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.index', compact('data', 'orders'));
    }

    public function create()
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'pl_number'    => 'nullable|string',
            'size'         => 'nullable|string',
            'mau'          => 'nullable|string',
            'kich'         => 'nullable|string',
            'cong_doan'    => 'nullable|string',
            'sl_don_hang'  => 'nullable|numeric',
            'sl_san_xuat'  => 'nullable|numeric',
        ]);
        OrderTracking::create($validated);
        return redirect()->route('admin.order-tracking.index')->with('success', 'Thêm tracking thành công.');
    }

    public function edit(OrderTracking $orderTracking)
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orderTracking', 'orders'));
    }

    public function update(Request $request, OrderTracking $orderTracking)
    {
        $validated = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'pl_number'    => 'nullable|string',
            'size'         => 'nullable|string',
            'mau'          => 'nullable|string',
            'kich'         => 'nullable|string',
            'cong_doan'    => 'nullable|string',
            'sl_don_hang'  => 'nullable|numeric',
            'sl_san_xuat'  => 'nullable|numeric',
        ]);
        $orderTracking->update($validated);
        return redirect()->route('admin.order-tracking.index')->with('success', 'Cập nhật tracking thành công.');
    }

    public function destroy(OrderTracking $orderTracking)
    {
        $orderTracking->delete();
        return redirect()->route('admin.order-tracking.index')->with('success', 'Xóa tracking thành công.');
    }
}
