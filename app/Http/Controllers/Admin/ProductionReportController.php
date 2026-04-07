<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionReport;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $data = ProductionReport::when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.production-reports.index', compact('data'));
    }

    public function create()
    {
        return view('admin.production-reports.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cong_doan'    => 'nullable|string',
            'ngay_sx'      => 'required|date',
            'ca'           => 'nullable|string',
            'ma_nv'        => 'nullable|string',
            'lenh_sx'      => 'nullable|string',
            'mau'          => 'nullable|string',
            'size'         => 'nullable|string',
            'dinh_muc'     => 'nullable|numeric',
            'so_band'      => 'nullable|integer',
            'ns_8h_1may'   => 'nullable|numeric',
            'ns_gio_may'   => 'nullable|numeric',
            'sl_dat'       => 'nullable|numeric',
            'sl_hu'        => 'nullable|numeric',
            'so_may'       => 'nullable|integer',
            'gio_sx'       => 'nullable|numeric',
            'sl_yard_met'  => 'nullable|numeric',
            'van_de'       => 'nullable|string',
        ]);
        ProductionReport::create($validated);
        return redirect()->route('admin.production-reports.index')->with('success', 'Thêm báo cáo thành công.');
    }

    public function edit(ProductionReport $productionReport)
    {
        return view('admin.production-reports.form', compact('productionReport'));
    }

    public function update(Request $request, ProductionReport $productionReport)
    {
        $validated = $request->validate([
            'cong_doan'    => 'nullable|string',
            'ngay_sx'      => 'required|date',
            'ca'           => 'nullable|string',
            'ma_nv'        => 'nullable|string',
            'lenh_sx'      => 'nullable|string',
            'mau'          => 'nullable|string',
            'size'         => 'nullable|string',
            'dinh_muc'     => 'nullable|numeric',
            'so_band'      => 'nullable|integer',
            'ns_8h_1may'   => 'nullable|numeric',
            'ns_gio_may'   => 'nullable|numeric',
            'sl_dat'       => 'nullable|numeric',
            'sl_hu'        => 'nullable|numeric',
            'so_may'       => 'nullable|integer',
            'gio_sx'       => 'nullable|numeric',
            'sl_yard_met'  => 'nullable|numeric',
            'van_de'       => 'nullable|string',
        ]);
        $productionReport->update($validated);
        return redirect()->route('admin.production-reports.index')->with('success', 'Cập nhật báo cáo thành công.');
    }

    public function destroy(ProductionReport $productionReport)
    {
        $productionReport->delete();
        return redirect()->route('admin.production-reports.index')->with('success', 'Xóa báo cáo thành công.');
    }
}
