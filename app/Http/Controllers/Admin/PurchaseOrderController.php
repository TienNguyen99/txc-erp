<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\NhaCungCap;
use App\Models\DanhMucHangHoa;
use App\Models\WarehouseTransaction;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $data = PurchaseOrder::with('nhaCungCap')
            ->withCount('items')
            ->when($request->search, fn($q, $s) =>
                $q->where('so_po', 'like', "%$s%"))
            ->when($request->trang_thai, fn($q, $s) =>
                $q->where('trang_thai', $s))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.purchase-orders.index', compact('data'));
    }

    public function create()
    {
        $nccList = NhaCungCap::where('active', true)->orderBy('ten_ncc')->get();
        $nvlList = DanhMucHangHoa::orderBy('ma_hh')->get(['id', 'ma_hh', 'ten_hh', 'don_vi', 'gia_nvl']);
        $soPo    = PurchaseOrder::generateSoPo();
        return view('admin.purchase-orders.form', compact('nccList', 'nvlList', 'soPo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'so_po'              => 'required|string|unique:purchase_orders,so_po',
            'nha_cung_cap_id'    => 'nullable|exists:nha_cung_cap,id',
            'ngay_dat'           => 'required|date',
            'ngay_giao_du_kien'  => 'nullable|date',
            'items'              => 'required|array|min:1',
            'items.*.ma_hh'      => 'required|string',
            'items.*.so_luong'   => 'required|numeric|min:0.01',
            'items.*.don_gia'    => 'nullable|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'so_po'             => $request->so_po,
            'nha_cung_cap_id'   => $request->nha_cung_cap_id,
            'ngay_dat'          => $request->ngay_dat,
            'ngay_giao_du_kien' => $request->ngay_giao_du_kien,
            'trang_thai'        => 'draft',
            'ghi_chu'           => $request->ghi_chu,
            'created_by'        => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $hh = DanhMucHangHoa::where('ma_hh', $item['ma_hh'])->first();
            $po->items()->create([
                'ma_hh'    => $item['ma_hh'],
                'ten_hh'   => $hh?->ten_hh ?? $item['ten_hh'] ?? '',
                'don_vi'   => $hh?->don_vi ?? 'Yard',
                'so_luong' => $item['so_luong'],
                'don_gia'  => $item['don_gia'] ?? 0,
                'ghi_chu'  => $item['ghi_chu'] ?? null,
            ]);
        }

        return redirect()->route('admin.purchase-orders.show', $po)
            ->with('success', "Tạo PO {$po->so_po} thành công.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.hangHoa', 'nhaCungCap', 'createdBy');
        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['trang_thai' => 'required|in:draft,sent,confirmed,received,cancelled']);
        $purchaseOrder->update(['trang_thai' => $request->trang_thai]);

        // Khi nhận hàng: cập nhật tồn kho (nhập kho NVL)
        if ($request->trang_thai === 'received') {
            $purchaseOrder->update(['ngay_nhan_thuc_te' => now()]);
            foreach ($purchaseOrder->items as $item) {
                WarehouseTransaction::create([
                    'loai'       => 'nhap',
                    'ma_hh'      => $item->ma_hh,
                    'lenh_sx'    => $purchaseOrder->so_po,
                    'mau'        => null,
                    'so_luong'   => $item->so_luong,
                    'don_vi'     => $item->don_vi ?? 'Yard',
                    'ngay_gd'    => now()->toDateString(),
                    'ghi_chu'    => "Nhập NVL theo PO: {$purchaseOrder->so_po}",
                    'created_by' => auth()->id(),
                ]);
                $item->update(['da_nhan' => $item->so_luong]);
            }
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái PO.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->trang_thai === 'received') {
            return redirect()->back()->with('error', 'Không thể xóa PO đã nhận hàng.');
        }
        $purchaseOrder->delete();
        return redirect()->route('admin.purchase-orders.index')->with('success', 'Đã xóa PO.');
    }

    /** Tạo PO nhanh từ BOM (gợi ý NVL thiếu) */
    public function createFromBom(Request $request)
    {
        $nccList = NhaCungCap::where('active', true)->orderBy('ten_ncc')->get();
        $soPo    = PurchaseOrder::generateSoPo();

        // Tính NVL thiếu từ tồn kho
        $nvlThieu = DanhMucHangHoa::whereRaw('ton_toi_thieu > 0')->get()->map(function ($hh) {
            $tonKho = WarehouseTransaction::where('ma_hh', $hh->ma_hh)->nhapKho()->sum('so_luong')
                    - WarehouseTransaction::where('ma_hh', $hh->ma_hh)->xuatKho()->sum('so_luong');
            $canMua = max(0, $hh->ton_toi_thieu - $tonKho);
            return [
                'ma_hh'    => $hh->ma_hh,
                'ten_hh'   => $hh->ten_hh,
                'don_vi'   => $hh->don_vi ?? 'Yard',
                'ton_kho'  => $tonKho,
                'toi_thieu'=> $hh->ton_toi_thieu,
                'can_mua'  => $canMua,
                'don_gia'  => $hh->gia_nvl ?? 0,
            ];
        })->filter(fn($x) => $x['can_mua'] > 0)->values();

        $nvlList = DanhMucHangHoa::orderBy('ma_hh')->get(['id', 'ma_hh', 'ten_hh', 'don_vi', 'gia_nvl']);

        return view('admin.purchase-orders.form', compact('nccList', 'nvlList', 'soPo', 'nvlThieu'));
    }
}
