<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Models\DinhMucNvl;
use Illuminate\Http\Request;

class DinhMucNvlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get products that have BOMs, or just list all products that might need BOMs.
        $query = DanhMucHangHoa::withCount('dinhMucNvl')->orderBy('id', 'desc');
        if ($request->get('missing') === 'bom') {
            $query->whereDoesntHave('dinhMucNvl');
        }
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($sub) use ($search) {
                $sub->where('ma_hh', 'like', "%{$search}%")
                    ->orWhere('ten_hh', 'like', "%{$search}%");
            });
        }
        
        $products = $query->paginate(20);
        return view('admin.dinh-muc-nvl.index', compact('products'));
    }

    /**
     * Display the specified resource.
     * Here, $id is the product ID (san_pham_id)
     */
    public function show($id)
    {
        $sanPham = DanhMucHangHoa::findOrFail($id);
        $boms = DinhMucNvl::where('san_pham_id', $id)->with('nguyenLieu')->get();
        
        // Prepare list of materials for selection
        $materials = DanhMucHangHoa::where('id', '!=', $id)->get();
        
        return view('admin.dinh-muc-nvl.show', compact('sanPham', 'boms', 'materials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $san_pham_id)
    {
        $request->validate([
            'nguyen_lieu_id' => 'required|exists:danh_muc_hang_hoa,id',
            'so_luong'       => 'required|numeric|min:0.0001',
            'ti_le_hao_hut'  => 'nullable|numeric|min:0|max:100',
            'cong_doan'      => 'nullable|string|max:255',
        ]);

        DinhMucNvl::updateOrCreate(
            [
                'san_pham_id' => $san_pham_id,
                'nguyen_lieu_id' => $request->nguyen_lieu_id,
                'cong_doan' => $request->cong_doan,
            ],
            [
                'so_luong' => $request->so_luong,
                'ti_le_hao_hut' => $request->ti_le_hao_hut ?? 0,
                'ghi_chu' => $request->ghi_chu,
            ]
        );

        return back()->with('success', 'Đã thêm nguyên vật liệu vào định mức!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($san_pham_id, $id)
    {
        DinhMucNvl::where('id', $id)->where('san_pham_id', $san_pham_id)->delete();
        return back()->with('success', 'Đã xóa nguyên vật liệu khỏi định mức!');
    }
}
