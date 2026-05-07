<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuyTrinhSanXuat;
use App\Models\DanhMucHangHoa;
use Illuminate\Http\Request;

class QuyTrinhSanXuatController extends Controller
{
    public function index()
    {
        $quyTrinhs = QuyTrinhSanXuat::orderBy('id', 'desc')->get();
        return view('admin.quy-trinh-san-xuat.index', compact('quyTrinhs'));
    }

    public function create()
    {
        $products = DanhMucHangHoa::select('ma_hh', 'ten_hh')->get();
        return view('admin.quy-trinh-san-xuat.form', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_quy_trinh' => 'required|unique:quy_trinh_san_xuat',
            'ten_quy_trinh' => 'required',
            'ngay_hieu_luc' => 'nullable|date',
            'trang_thai' => 'required',
            'san_pham_ap_dung' => 'nullable|array',
            'flow_data' => 'nullable|string',
            'ghi_chu' => 'nullable',
        ]);

        if (isset($validated['flow_data'])) {
            $validated['flow_data'] = json_decode($validated['flow_data'], true);
        }

        QuyTrinhSanXuat::create($validated);

        return redirect()->route('admin.quy-trinh-san-xuat.index')->with('success', 'Tạo quy trình thành công');
    }

    public function edit(QuyTrinhSanXuat $quyTrinhSanXuat)
    {
        $products = DanhMucHangHoa::select('ma_hh', 'ten_hh')->get();
        return view('admin.quy-trinh-san-xuat.form', [
            'quyTrinh' => $quyTrinhSanXuat,
            'products' => $products
        ]);
    }

    public function update(Request $request, QuyTrinhSanXuat $quyTrinhSanXuat)
    {
        $validated = $request->validate([
            'ma_quy_trinh' => 'required|unique:quy_trinh_san_xuat,ma_quy_trinh,' . $quyTrinhSanXuat->id,
            'ten_quy_trinh' => 'required',
            'ngay_hieu_luc' => 'nullable|date',
            'trang_thai' => 'required',
            'san_pham_ap_dung' => 'nullable|array',
            'flow_data' => 'nullable|string',
            'ghi_chu' => 'nullable',
        ]);

        if (isset($validated['flow_data'])) {
            $validated['flow_data'] = json_decode($validated['flow_data'], true);
        }

        $quyTrinhSanXuat->update($validated);

        return redirect()->route('admin.quy-trinh-san-xuat.index')->with('success', 'Cập nhật quy trình thành công');
    }

    public function destroy(QuyTrinhSanXuat $quyTrinhSanXuat)
    {
        $quyTrinhSanXuat->delete();
        return redirect()->route('admin.quy-trinh-san-xuat.index')->with('success', 'Xóa quy trình thành công');
    }
}
