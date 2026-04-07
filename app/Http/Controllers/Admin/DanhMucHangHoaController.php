<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Imports\DanhMucHangHoaImport;
use App\Exports\DanhMucHangHoaExport;
use App\Exports\DanhMucHangHoaTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DanhMucHangHoaController extends Controller
{
    public function index(Request $request)
    {
        $data = DanhMucHangHoa::when($request->search, fn($q, $s) => $q->where('ma_hh', 'like', "%$s%")->orWhere('ten_hh', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.hang-hoa.index', compact('data'));
    }

    public function create()
    {
        return view('admin.hang-hoa.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_hh'    => 'required|string|unique:danh_muc_hang_hoa,ma_hh',
            'ten_hh'   => 'required|string|max:255',
            'mau'      => 'nullable|string',
            'kich_co'  => 'nullable|string',
            'nhom_hh'  => 'nullable|string',
            'don_vi'   => 'nullable|string',
            'don_gia'  => 'nullable|numeric|min:0',
            'hinh_anh' => 'nullable|image|max:2048',
            'mo_ta'    => 'nullable|string',
            'active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('hinh_anh')) {
            $validated['hinh_anh'] = $request->file('hinh_anh')->store('hang-hoa', 'public');
        }

        $validated['active'] = $request->has('active');
        DanhMucHangHoa::create($validated);
        return redirect()->route('admin.hang-hoa.index')->with('success', 'Thêm hàng hóa thành công.');
    }

    public function edit(DanhMucHangHoa $hangHoa)
    {
        return view('admin.hang-hoa.form', compact('hangHoa'));
    }

    public function update(Request $request, DanhMucHangHoa $hangHoa)
    {
        $validated = $request->validate([
            'ma_hh'    => 'required|string|unique:danh_muc_hang_hoa,ma_hh,' . $hangHoa->id,
            'ten_hh'   => 'required|string|max:255',
            'mau'      => 'nullable|string',
            'kich_co'  => 'nullable|string',
            'nhom_hh'  => 'nullable|string',
            'don_vi'   => 'nullable|string',
            'don_gia'  => 'nullable|numeric|min:0',
            'hinh_anh' => 'nullable|image|max:2048',
            'mo_ta'    => 'nullable|string',
            'active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('hinh_anh')) {
            $validated['hinh_anh'] = $request->file('hinh_anh')->store('hang-hoa', 'public');
        }

        $validated['active'] = $request->has('active');
        $hangHoa->update($validated);
        return redirect()->route('admin.hang-hoa.index')->with('success', 'Cập nhật hàng hóa thành công.');
    }

    public function destroy(DanhMucHangHoa $hangHoa)
    {
        $hangHoa->delete();
        return redirect()->route('admin.hang-hoa.index')->with('success', 'Xóa hàng hóa thành công.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        Excel::import(new DanhMucHangHoaImport, $request->file('file'));
        return redirect()->route('admin.hang-hoa.index')->with('success', 'Import hàng hóa thành công.');
    }

    public function export()
    {
        return Excel::download(new DanhMucHangHoaExport, 'danh-muc-hang-hoa.xlsx');
    }

    public function template()
    {
        return Excel::download(new DanhMucHangHoaTemplateExport, 'template-hang-hoa.xlsx');
    }
}
