<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Imports\DanhMucHangHoaImport;
use App\Exports\DanhMucHangHoaExport;
use App\Exports\DanhMucHangHoaTemplateExport;
use App\Support\ItemCode;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DanhMucHangHoaController extends Controller
{
    public function index(Request $request)
    {
        $missing = $request->get('missing');
        $data = DanhMucHangHoa::query()
                    ->when($request->search, function ($q, $s) {
                        $q->where(function ($sub) use ($s) {
                            $sub->where('ma_hh', 'like', "%$s%")
                                ->orWhere('ten_hh', 'like', "%$s%");
                        });
                    })
                    ->when($missing === 'price', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('don_gia')
                                ->orWhere('don_gia', '<=', 0);
                        });
                    })
                    ->when($missing === 'carton_norm', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('dinh_muc_thung')
                                ->orWhere('dinh_muc_thung', '<=', 0);
                        });
                    })
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.hang-hoa.index', compact('data'));
    }

    public function create()
    {
        return view('admin.hang-hoa.form');
    }

    public function store(Request $request)
    {
        $request->merge(['ma_hh' => ItemCode::normalize($request->input('ma_hh'))]);

        $validated = $request->validate([
            'ma_hh'    => ['required', 'string', 'regex:' . ItemCode::VALIDATION_REGEX, 'unique:danh_muc_hang_hoa,ma_hh'],
            'ten_hh'   => 'required|string|max:255',
            'mau'      => 'nullable|string',
            'kich_co'  => 'nullable|string',
            'nhom_hh'  => 'nullable|string',
            'don_vi'   => 'nullable|string',
            'don_gia'          => 'nullable|numeric|min:0',
            'quy_cach'         => 'nullable|string',
            'yards_per_roll'   => 'nullable|numeric|min:0',
            'rolls_per_carton' => 'nullable|integer|min:1',
            'dinh_muc_thung'   => 'nullable|integer|min:1',
            'net_weight'     => 'nullable|numeric|min:0',
            'gross_weight'   => 'nullable|numeric|min:0',
            'hinh_anh'          => 'nullable|image|max:2048',
            'mo_ta'             => 'nullable|string',
            'active'            => 'nullable|boolean',
            'nha_cung_cap_id'   => 'nullable|exists:nha_cung_cap,id',
            'gia_nvl'           => 'nullable|numeric|min:0',
            'ton_toi_thieu'     => 'nullable|integer|min:0',
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
        $prevHangHoaId = DanhMucHangHoa::where('id', '<', $hangHoa->id)->max('id');
        $nextHangHoaId = DanhMucHangHoa::where('id', '>', $hangHoa->id)->min('id');

        return view('admin.hang-hoa.form', compact('hangHoa', 'prevHangHoaId', 'nextHangHoaId'));
    }

    public function update(Request $request, DanhMucHangHoa $hangHoa)
    {
        $request->merge(['ma_hh' => ItemCode::normalize($request->input('ma_hh'))]);

        $validated = $request->validate([
            'ma_hh'    => ['required', 'string', 'regex:' . ItemCode::VALIDATION_REGEX, 'unique:danh_muc_hang_hoa,ma_hh,' . $hangHoa->id],
            'ten_hh'   => 'required|string|max:255',
            'mau'      => 'nullable|string',
            'kich_co'  => 'nullable|string',
            'nhom_hh'  => 'nullable|string',
            'don_vi'   => 'nullable|string',
            'don_gia'          => 'nullable|numeric|min:0',
            'quy_cach'         => 'nullable|string',
            'yards_per_roll'   => 'nullable|numeric|min:0',
            'rolls_per_carton' => 'nullable|integer|min:1',
            'dinh_muc_thung'   => 'nullable|integer|min:1',
            'net_weight'     => 'nullable|numeric|min:0',
            'gross_weight'   => 'nullable|numeric|min:0',
            'hinh_anh'          => 'nullable|image|max:2048',
            'mo_ta'             => 'nullable|string',
            'active'            => 'nullable|boolean',
            'nha_cung_cap_id'   => 'nullable|exists:nha_cung_cap,id',
            'gia_nvl'           => 'nullable|numeric|min:0',
            'ton_toi_thieu'     => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('hinh_anh')) {
            $validated['hinh_anh'] = $request->file('hinh_anh')->store('hang-hoa', 'public');
        }

        $validated['active'] = $request->has('active');
        $hangHoa->update($validated);

        if ($request->input('after_save') === 'next') {
            $nextHangHoaId = $request->integer('next_id');
            if (!$nextHangHoaId) {
                $nextHangHoaId = DanhMucHangHoa::where('id', '>', $hangHoa->id)->min('id');
            }

            if ($nextHangHoaId) {
                return redirect()->route('admin.hang-hoa.edit', $nextHangHoaId)
                    ->with('success', 'Đã lưu. Chuyển sang hàng tiếp theo.');
            }
        }

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
