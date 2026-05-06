<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucKhachHang;
use Illuminate\Http\Request;

class DanhMucKhachHangController extends Controller
{
    public function index(Request $request)
    {
        $data = DanhMucKhachHang::when($request->search, fn($q, $s) => $q->where('ma_kh', 'like', "%$s%")->orWhere('ten_kh', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.khach-hang.index', compact('data'));
    }

    public function create()
    {
        return view('admin.khach-hang.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_kh'          => 'required|string|unique:danh_muc_khach_hang,ma_kh',
            'ten_kh'         => 'required|string|max:255',
            'nguoi_lien_he'  => 'nullable|string',
            'sdt'            => 'nullable|string',
            'email'          => 'nullable|email',
            'dia_chi'        => 'nullable|string',
            'ma_so_thue'     => 'nullable|string',
            'ghi_chu'        => 'nullable|string',
            'active'         => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        DanhMucKhachHang::create($validated);
        return redirect()->route('admin.khach-hang.index')->with('success', 'Thêm khách hàng thành công.');
    }

    public function edit(DanhMucKhachHang $khachHang)
    {
        return view('admin.khach-hang.form', compact('khachHang'));
    }

    public function update(Request $request, DanhMucKhachHang $khachHang)
    {
        $validated = $request->validate([
            'ma_kh'          => 'required|string|unique:danh_muc_khach_hang,ma_kh,' . $khachHang->id,
            'ten_kh'         => 'required|string|max:255',
            'nguoi_lien_he'  => 'nullable|string',
            'sdt'            => 'nullable|string',
            'email'          => 'nullable|email',
            'dia_chi'        => 'nullable|string',
            'ma_so_thue'     => 'nullable|string',
            'ghi_chu'        => 'nullable|string',
            'active'         => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $khachHang->update($validated);
        return redirect()->route('admin.khach-hang.index')->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function destroy(DanhMucKhachHang $khachHang)
    {
        $khachHang->delete();
        return redirect()->route('admin.khach-hang.index')->with('success', 'Xóa khách hàng thành công.');
    }

    public function getGroups(Request $request)
    {
        $khachHangId = $request->khach_hang_id;
        if (!$khachHangId) {
            return response()->json([]);
        }
        $groups = \App\Models\KhachHangNhomHang::where('khach_hang_id', $khachHangId)->get();
        return response()->json($groups);
    }

    public function saveGroups(Request $request)
    {
        $request->validate([
            'khach_hang_id' => 'required|exists:danh_muc_khach_hang,id',
            'ma_nhom' => 'required|array',
            'ma_nhom.*' => 'required|string|max:50',
            'ten_nhom' => 'nullable|array',
            'ten_nhom.*' => 'nullable|string|max:255',
        ]);

        $khachHangId = $request->khach_hang_id;
        $maNhoms = $request->ma_nhom;
        $tenNhoms = $request->ten_nhom ?? [];

        // Lấy danh sách ID đã có để xem cần xóa cái nào không
        $existing = \App\Models\KhachHangNhomHang::where('khach_hang_id', $khachHangId)->pluck('id')->toArray();
        $keepIds = [];

        foreach ($maNhoms as $index => $maNhom) {
            $maNhom = trim($maNhom);
            if (empty($maNhom)) continue;
            $tenNhom = isset($tenNhoms[$index]) ? trim($tenNhoms[$index]) : null;

            $group = \App\Models\KhachHangNhomHang::updateOrCreate(
                ['khach_hang_id' => $khachHangId, 'ma_nhom' => $maNhom],
                ['ten_nhom' => $tenNhom]
            );
            $keepIds[] = $group->id;
        }

        // Xóa những nhóm cũ không còn gửi lên
        $toDelete = array_diff($existing, $keepIds);
        if (!empty($toDelete)) {
            \App\Models\KhachHangNhomHang::whereIn('id', $toDelete)->delete();
        }

        return redirect()->back()->with('success', 'Đã lưu cấu hình Nhóm hàng hóa.');
    }
}
