<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProductionReport;
use App\Models\WarehouseTransaction;
use App\Models\DanhMucHangHoa;
use App\Models\DanhMucKhachHang;
use App\Imports\DanhMucHangHoaImport;
use App\Exports\DanhMucHangHoaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    // ─── Dashboard tổng quan ───
    public function index()
    {
        $stats = [
            'users'                  => User::count(),
            'orders'                 => Order::count(),
            'order_tracking'         => OrderTracking::count(),
            'production_reports'     => ProductionReport::count(),
            'warehouse_transactions' => WarehouseTransaction::count(),
        ];

        $recentOrders     = Order::latest()->take(5)->get();
        $recentProduction = ProductionReport::latest()->take(5)->get();
        $recentWarehouse  = WarehouseTransaction::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentProduction', 'recentWarehouse'));
    }

    // ═══════════════════════════════════════
    //  USERS
    // ═══════════════════════════════════════
    public function users(Request $request)
    {
        $data = User::when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.users.index', compact('data'));
    }

    public function usersCreate()
    {
        return view('admin.users.form');
    }

    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        $validated['password'] = bcrypt($validated['password']);
        User::create($validated);
        return redirect()->route('admin.users')->with('success', 'Thêm user thành công.');
    }

    public function usersEdit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);
        if ($validated['password']) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        return redirect()->route('admin.users')->with('success', 'Cập nhật user thành công.');
    }

    public function usersDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Xóa user thành công.');
    }

    // ═══════════════════════════════════════
    //  ORDERS
    // ═══════════════════════════════════════
    public function orders(Request $request)
    {
        $data = Order::when($request->search, fn($q, $s) => $q->where('job_no', 'like', "%$s%")->orWhere('fty_po', 'like', "%$s%"))
                     ->when($request->status, fn($q, $s) => $q->where('status', $s))
                     ->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('data'));
    }

    public function ordersCreate()
    {
        $khachHangs = DanhMucKhachHang::where('active', true)->pluck('ten_kh', 'id');
        return view('admin.orders.form', compact('khachHangs'));
    }

    public function ordersStore(Request $request)
    {
        $validated = $request->validate([
            'khach_hang_id' => 'nullable|exists:danh_muc_khach_hang,id',
            'job_no'        => 'required|string|unique:orders,job_no',
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'qty'           => 'nullable|numeric',
            'unit'          => 'nullable|string',
            'size'          => 'nullable|string',
            'yrd'           => 'nullable|numeric',
            'can_giao_1'    => 'nullable|numeric',
            'can_giao_2'    => 'nullable|numeric',
            'pl_number'     => 'nullable|string',
            'tagtime_etc'   => 'nullable|date',
            'sig_need_date' => 'nullable|date',
            'chart'         => 'nullable|string',
            'price_usd_auto'=> 'nullable|numeric',
            'price_usd'     => 'nullable|numeric',
            'to_khai'       => 'nullable|string',
            'status'        => 'required|in:pending,in_production,done,shipped',
        ]);
        Order::create($validated);
        return redirect()->route('admin.orders')->with('success', 'Thêm đơn hàng thành công.');
    }

    public function ordersEdit(Order $order)
    {
        $khachHangs = DanhMucKhachHang::where('active', true)->pluck('ten_kh', 'id');
        return view('admin.orders.form', compact('order', 'khachHangs'));
    }

    public function ordersUpdate(Request $request, Order $order)
    {
        $validated = $request->validate([
            'khach_hang_id' => 'nullable|exists:danh_muc_khach_hang,id',
            'job_no'        => 'required|string|unique:orders,job_no,' . $order->id,
            'fty_po'        => 'nullable|string',
            'im_number'     => 'nullable|string',
            'color'         => 'nullable|string',
            'qty'           => 'nullable|numeric',
            'unit'          => 'nullable|string',
            'size'          => 'nullable|string',
            'yrd'           => 'nullable|numeric',
            'can_giao_1'    => 'nullable|numeric',
            'can_giao_2'    => 'nullable|numeric',
            'pl_number'     => 'nullable|string',
            'tagtime_etc'   => 'nullable|date',
            'sig_need_date' => 'nullable|date',
            'chart'         => 'nullable|string',
            'price_usd_auto'=> 'nullable|numeric',
            'price_usd'     => 'nullable|numeric',
            'to_khai'       => 'nullable|string',
            'status'        => 'required|in:pending,in_production,done,shipped',
        ]);
        $order->update($validated);
        return redirect()->route('admin.orders')->with('success', 'Cập nhật đơn hàng thành công.');
    }

    public function ordersDestroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders')->with('success', 'Xóa đơn hàng thành công.');
    }

    // ═══════════════════════════════════════
    //  ORDER TRACKING
    // ═══════════════════════════════════════
    public function orderTracking(Request $request)
    {
        $data = OrderTracking::with('order')
                    ->when($request->search, fn($q, $s) => $q->where('pl_number', 'like', "%$s%")->orWhere('mau', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.index', compact('data', 'orders'));
    }

    public function orderTrackingCreate()
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orders'));
    }

    public function orderTrackingStore(Request $request)
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
        return redirect()->route('admin.order-tracking')->with('success', 'Thêm tracking thành công.');
    }

    public function orderTrackingEdit(OrderTracking $orderTracking)
    {
        $orders = Order::pluck('job_no', 'id');
        return view('admin.order-tracking.form', compact('orderTracking', 'orders'));
    }

    public function orderTrackingUpdate(Request $request, OrderTracking $orderTracking)
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
        return redirect()->route('admin.order-tracking')->with('success', 'Cập nhật tracking thành công.');
    }

    public function orderTrackingDestroy(OrderTracking $orderTracking)
    {
        $orderTracking->delete();
        return redirect()->route('admin.order-tracking')->with('success', 'Xóa tracking thành công.');
    }

    // ═══════════════════════════════════════
    //  PRODUCTION REPORTS
    // ═══════════════════════════════════════
    public function productionReports(Request $request)
    {
        $data = ProductionReport::when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.production-reports.index', compact('data'));
    }

    public function productionReportsCreate()
    {
        return view('admin.production-reports.form');
    }

    public function productionReportsStore(Request $request)
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
        return redirect()->route('admin.production-reports')->with('success', 'Thêm báo cáo thành công.');
    }

    public function productionReportsEdit(ProductionReport $productionReport)
    {
        return view('admin.production-reports.form', compact('productionReport'));
    }

    public function productionReportsUpdate(Request $request, ProductionReport $productionReport)
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
        return redirect()->route('admin.production-reports')->with('success', 'Cập nhật báo cáo thành công.');
    }

    public function productionReportsDestroy(ProductionReport $productionReport)
    {
        $productionReport->delete();
        return redirect()->route('admin.production-reports')->with('success', 'Xóa báo cáo thành công.');
    }

    // ═══════════════════════════════════════
    //  WAREHOUSE TRANSACTIONS
    // ═══════════════════════════════════════
    public function warehouseTransactions(Request $request)
    {
        $data = WarehouseTransaction::when($request->search, fn($q, $s) => $q->where('lenh_sx', 'like', "%$s%")->orWhere('ma_nv', 'like', "%$s%"))
                    ->when($request->cong_doan, fn($q, $cd) => $q->where('cong_doan', $cd))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.warehouse-transactions.index', compact('data'));
    }

    public function warehouseTransactionsCreate()
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('hangHoas'));
    }

    public function warehouseTransactionsStore(Request $request)
    {
        $validated = $request->validate([
            'cong_doan'   => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh'       => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay'        => 'required|date',
            'size'        => 'nullable|string',
            'mau'         => 'nullable|string',
            'so_luong'    => 'required|numeric|min:0.01',
            'ma_nv'       => 'nullable|string',
            'lenh_sx'     => 'nullable|string',
            'note'        => 'nullable|string',
        ]);
        WarehouseTransaction::create($validated);
        return redirect()->route('admin.warehouse-transactions')->with('success', 'Thêm giao dịch kho thành công.');
    }

    public function warehouseTransactionsEdit(WarehouseTransaction $warehouseTransaction)
    {
        $hangHoas = DanhMucHangHoa::where('active', true)->pluck('ten_hh', 'id');
        return view('admin.warehouse-transactions.form', compact('warehouseTransaction', 'hangHoas'));
    }

    public function warehouseTransactionsUpdate(Request $request, WarehouseTransaction $warehouseTransaction)
    {
        $validated = $request->validate([
            'cong_doan'   => 'required|in:NHAPKHO,XUATKHO',
            'ma_hh'       => 'nullable|string',
            'hang_hoa_id' => 'nullable|exists:danh_muc_hang_hoa,id',
            'ngay'        => 'required|date',
            'size'        => 'nullable|string',
            'mau'         => 'nullable|string',
            'so_luong'    => 'required|numeric|min:0.01',
            'ma_nv'       => 'nullable|string',
            'lenh_sx'     => 'nullable|string',
            'note'        => 'nullable|string',
        ]);
        $warehouseTransaction->update($validated);
        return redirect()->route('admin.warehouse-transactions')->with('success', 'Cập nhật giao dịch kho thành công.');
    }

    public function warehouseTransactionsDestroy(WarehouseTransaction $warehouseTransaction)
    {
        $warehouseTransaction->delete();
        return redirect()->route('admin.warehouse-transactions')->with('success', 'Xóa giao dịch kho thành công.');
    }

    // ═══════════════════════════════════════
    //  DANH MỤC HÀNG HÓA
    // ═══════════════════════════════════════
    public function hangHoa(Request $request)
    {
        $data = DanhMucHangHoa::when($request->search, fn($q, $s) => $q->where('ma_hh', 'like', "%$s%")->orWhere('ten_hh', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.hang-hoa.index', compact('data'));
    }

    public function hangHoaCreate()
    {
        return view('admin.hang-hoa.form');
    }

    public function hangHoaStore(Request $request)
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
        return redirect()->route('admin.hang-hoa')->with('success', 'Thêm hàng hóa thành công.');
    }

    public function hangHoaEdit(DanhMucHangHoa $hangHoa)
    {
        return view('admin.hang-hoa.form', compact('hangHoa'));
    }

    public function hangHoaUpdate(Request $request, DanhMucHangHoa $hangHoa)
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
        return redirect()->route('admin.hang-hoa')->with('success', 'Cập nhật hàng hóa thành công.');
    }

    public function hangHoaDestroy(DanhMucHangHoa $hangHoa)
    {
        $hangHoa->delete();
        return redirect()->route('admin.hang-hoa')->with('success', 'Xóa hàng hóa thành công.');
    }

    public function hangHoaImport(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        Excel::import(new DanhMucHangHoaImport, $request->file('file'));
        return redirect()->route('admin.hang-hoa')->with('success', 'Import hàng hóa thành công.');
    }

    public function hangHoaExport()
    {
        return Excel::download(new DanhMucHangHoaExport, 'danh-muc-hang-hoa.xlsx');
    }

    // ═══════════════════════════════════════
    //  DANH MỤC KHÁCH HÀNG
    // ═══════════════════════════════════════
    public function khachHang(Request $request)
    {
        $data = DanhMucKhachHang::when($request->search, fn($q, $s) => $q->where('ma_kh', 'like', "%$s%")->orWhere('ten_kh', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.khach-hang.index', compact('data'));
    }

    public function khachHangCreate()
    {
        return view('admin.khach-hang.form');
    }

    public function khachHangStore(Request $request)
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
        return redirect()->route('admin.khach-hang')->with('success', 'Thêm khách hàng thành công.');
    }

    public function khachHangEdit(DanhMucKhachHang $khachHang)
    {
        return view('admin.khach-hang.form', compact('khachHang'));
    }

    public function khachHangUpdate(Request $request, DanhMucKhachHang $khachHang)
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
        return redirect()->route('admin.khach-hang')->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function khachHangDestroy(DanhMucKhachHang $khachHang)
    {
        $khachHang->delete();
        return redirect()->route('admin.khach-hang')->with('success', 'Xóa khách hàng thành công.');
    }
}
