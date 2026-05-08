<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;

class NhaCungCapController extends Controller
{
    public function index(Request $request)
    {
        $data = NhaCungCap::when($request->search, fn($q, $s) =>
                    $q->where('ma_ncc', 'like', "%$s%")->orWhere('ten_ncc', 'like', "%$s%"))
                ->withCount('hangHoa')
                ->withCount('purchaseOrders')
                ->latest()->paginate(15)->withQueryString();
        return view('admin.nha-cung-cap.index', compact('data'));
    }

    public function create()
    {
        return view('admin.nha-cung-cap.form');
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'ma_ncc'        => 'required|string|max:50|unique:nha_cung_cap,ma_ncc',
            'ten_ncc'       => 'required|string|max:255',
            'nguoi_lien_he' => 'nullable|string|max:255',
            'sdt'           => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'dia_chi'       => 'nullable|string',
            'ma_so_thue'    => 'nullable|string|max:50',
            'ghi_chu'       => 'nullable|string',
        ]);
        $v['active'] = $request->has('active');
        NhaCungCap::create($v);
        return redirect()->route('admin.nha-cung-cap.index')->with('success', 'Thêm nhà cung cấp thành công.');
    }

    public function edit(NhaCungCap $nhaCungCap)
    {
        return view('admin.nha-cung-cap.form', compact('nhaCungCap'));
    }

    public function update(Request $request, NhaCungCap $nhaCungCap)
    {
        $v = $request->validate([
            'ma_ncc'        => 'required|string|max:50|unique:nha_cung_cap,ma_ncc,' . $nhaCungCap->id,
            'ten_ncc'       => 'required|string|max:255',
            'nguoi_lien_he' => 'nullable|string|max:255',
            'sdt'           => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'dia_chi'       => 'nullable|string',
            'ma_so_thue'    => 'nullable|string|max:50',
            'ghi_chu'       => 'nullable|string',
        ]);
        $v['active'] = $request->has('active');
        $nhaCungCap->update($v);
        return redirect()->route('admin.nha-cung-cap.index')->with('success', 'Cập nhật nhà cung cấp thành công.');
    }

    public function destroy(NhaCungCap $nhaCungCap)
    {
        $nhaCungCap->delete();
        return redirect()->route('admin.nha-cung-cap.index')->with('success', 'Đã xóa nhà cung cấp.');
    }
}
