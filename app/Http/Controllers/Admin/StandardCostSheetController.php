<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMucHangHoa;
use App\Models\StandardCostLine;
use App\Models\StandardCostSheet;
use App\Services\StandardCostSheetService;
use App\Support\ItemCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StandardCostSheetController extends Controller
{
    public function __construct(private readonly StandardCostSheetService $costSheetService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $sheets = StandardCostSheet::query()
            ->with('product:id,ma_hh,ten_hh,don_vi')
            ->withCount('lines')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', fn($product) => $product
                    ->where('ma_hh', 'like', "%{$search}%")
                    ->orWhere('ten_hh', 'like', "%{$search}%"))
                    ->orWhere('version', 'like', "%{$search}%");
            })
            ->latest('effective_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $products = DanhMucHangHoa::query()
            ->where('active', true)
            ->orderBy('ma_hh')
            ->get(['id', 'ma_hh', 'ten_hh', 'don_vi']);

        return view('admin.standard-cost-sheets.index', compact('sheets', 'products', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:danh_muc_hang_hoa,id'],
            'version' => ['required', 'string', 'max:50', Rule::unique('standard_cost_sheets')->where('product_id', $request->input('product_id'))],
            'effective_date' => ['required', 'date'],
            'standard_output_qty' => ['required', 'numeric', 'min:0.0001'],
            'sale_price_vnd' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);
        $validated['created_by_id'] = $request->user()?->id;

        $sheet = StandardCostSheet::create($validated);

        return redirect()->route('admin.standard-cost-sheets.show', $sheet)
            ->with('success', 'Đã tạo bảng giá vốn định mức.');
    }

    public function show(StandardCostSheet $standardCostSheet)
    {
        $standardCostSheet->load('product', 'lines.item', 'createdBy');
        $calculation = $this->costSheetService->calculate($standardCostSheet);
        $materials = DanhMucHangHoa::query()
            ->where('active', true)
            ->whereKeyNot($standardCostSheet->product_id)
            ->orderBy('ma_hh')
            ->get(['id', 'ma_hh', 'ten_hh', 'don_vi', 'gia_nvl', 'don_gia']);

        return view('admin.standard-cost-sheets.show', compact('standardCostSheet', 'calculation', 'materials'));
    }

    public function update(Request $request, StandardCostSheet $standardCostSheet)
    {
        $standardCostSheet->update($request->validate([
            'version' => ['required', 'string', 'max:50', Rule::unique('standard_cost_sheets')->where('product_id', $standardCostSheet->product_id)->ignore($standardCostSheet)],
            'effective_date' => ['required', 'date'],
            'standard_output_qty' => ['required', 'numeric', 'min:0.0001'],
            'sale_price_vnd' => ['nullable', 'numeric', 'min:0'],
            'bank_interest_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bank_interest_basis' => ['required', Rule::in(array_keys(StandardCostSheet::BASES))],
            'commission_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_basis' => ['required', Rule::in(array_keys(StandardCostSheet::BASES))],
            'management_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'management_basis' => ['required', Rule::in(array_keys(StandardCostSheet::BASES))],
            'transport_cost_vnd' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]));

        return back()->with('success', 'Đã cập nhật cấu hình giá vốn.');
    }

    public function activate(StandardCostSheet $standardCostSheet)
    {
        DB::transaction(function () use ($standardCostSheet) {
            StandardCostSheet::where('product_id', $standardCostSheet->product_id)
                ->where('id', '!=', $standardCostSheet->id)
                ->where('status', 'active')
                ->update(['status' => 'archived']);
            $standardCostSheet->update(['status' => 'active']);
        });

        return back()->with('success', 'Đã áp dụng phiên bản giá vốn này.');
    }

    public function storeLine(Request $request, StandardCostSheet $standardCostSheet)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(StandardCostLine::CATEGORIES))],
            'item_id' => ['nullable', 'exists:danh_muc_hang_hoa,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'stage' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'waste_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'unit_price_vnd' => ['required', 'numeric', 'min:0'],
            'allocation_qty' => ['nullable', 'numeric', 'min:0.0001'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $standardCostSheet->lines()->create($validated);

        return back()->with('success', 'Đã thêm dòng chi phí.');
    }

    public function quickCreateItem(Request $request)
    {
        $request->merge(['ma_hh' => ItemCode::normalize($request->input('ma_hh'))]);
        $validated = $request->validate([
            'ma_hh' => ['required', 'string', 'regex:' . ItemCode::VALIDATION_REGEX, 'unique:danh_muc_hang_hoa,ma_hh'],
            'ten_hh' => ['required', 'string', 'max:255'],
            'nhom_hh' => ['nullable', 'string', 'max:255'],
            'don_vi' => ['nullable', 'string', 'max:50'],
            'gia_nvl' => ['nullable', 'numeric', 'min:0'],
        ]);
        $validated['active'] = true;

        $item = DanhMucHangHoa::create($validated);

        return response()->json([
            'message' => 'Đã thêm hàng hóa vào danh mục.',
            'item' => [
                'id' => $item->id,
                'ma_hh' => $item->ma_hh,
                'ten_hh' => $item->ten_hh,
                'don_vi' => $item->don_vi,
                'gia_nvl' => (float) $item->gia_nvl,
            ],
        ], 201);
    }

    public function destroyLine(StandardCostSheet $standardCostSheet, StandardCostLine $line)
    {
        abort_unless($line->standard_cost_sheet_id === $standardCostSheet->id, 404);
        $line->delete();

        return back()->with('success', 'Đã xóa dòng chi phí.');
    }

    public function destroy(StandardCostSheet $standardCostSheet)
    {
        abort_if($standardCostSheet->status === 'active', 422, 'Không thể xóa phiên bản đang áp dụng.');
        $standardCostSheet->delete();

        return redirect()->route('admin.standard-cost-sheets.index')
            ->with('success', 'Đã xóa bảng giá vốn định mức.');
    }
}
