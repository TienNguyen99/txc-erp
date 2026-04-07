@extends('layouts.app')

@section('css')
    <style>
        .stat-card {
            border-radius: 12px;
            padding: 1.5rem;
            color: #fff;
            transition: transform .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: .7;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-card .stat-label {
            font-size: .85rem;
            opacity: .85;
        }

        .bg-grad-1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .bg-grad-2 {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .bg-grad-3 {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .bg-grad-4 {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }

        .bg-grad-5 {
            background: linear-gradient(135deg, #fa709a, #fee140);
        }

        .quick-table th {
            font-size: .8rem;
        }

        .quick-table td {
            font-size: .85rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0" style="color:#1e3a5f">
                <i class="fa-solid fa-gauge-high me-2"></i>Admin Dashboard
            </h4>
            <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md col-sm-6">
                <a href="{{ route('admin.users') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number">{{ number_format($stats['users']) }}</div>
                                <div class="stat-label">Users</div>
                            </div>
                            <i class="fa-solid fa-users stat-icon"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md col-sm-6">
                <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number">{{ number_format($stats['orders']) }}</div>
                                <div class="stat-label">Đơn hàng</div>
                            </div>
                            <i class="fa-solid fa-file-invoice stat-icon"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md col-sm-6">
                <a href="{{ route('admin.order-tracking') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number">{{ number_format($stats['order_tracking']) }}</div>
                                <div class="stat-label">Order Tracking</div>
                            </div>
                            <i class="fa-solid fa-truck-fast stat-icon"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md col-sm-6">
                <a href="{{ route('admin.production-reports') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number">{{ number_format($stats['production_reports']) }}</div>
                                <div class="stat-label">Báo cáo SX</div>
                            </div>
                            <i class="fa-solid fa-industry stat-icon"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md col-sm-6">
                <a href="{{ route('admin.warehouse-transactions') }}" class="text-decoration-none">
                    <div class="stat-card bg-grad-5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number">{{ number_format($stats['warehouse_transactions']) }}</div>
                                <div class="stat-label">Giao dịch kho</div>
                            </div>
                            <i class="fa-solid fa-warehouse stat-icon"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent data tables --}}
        <div class="row g-3">

            {{-- Recent Orders --}}
            <div class="col-lg-6">
                <div class="card-page">
                    <h6 class="fw-bold mb-3" style="color:#1e3a5f">
                        <i class="fa-solid fa-file-invoice me-1"></i>Đơn hàng gần đây
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover quick-table mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Job No</th>
                                    <th>Fty PO</th>
                                    <th>Màu</th>
                                    <th>SL</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $o)
                                    <tr>
                                        <td>{{ $o->job_no }}</td>
                                        <td>{{ $o->fty_po }}</td>
                                        <td>{{ $o->color }}</td>
                                        <td>{{ number_format($o->qty, 2) }}</td>
                                        <td>
                                            @php $colors = ['pending'=>'warning','in_production'=>'info','done'=>'success','shipped'=>'primary']; @endphp
                                            <span
                                                class="badge bg-{{ $colors[$o->status] ?? 'secondary' }}">{{ $o->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Production Reports --}}
            <div class="col-lg-6">
                <div class="card-page">
                    <h6 class="fw-bold mb-3" style="color:#1e3a5f">
                        <i class="fa-solid fa-industry me-1"></i>Báo cáo SX gần đây
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover quick-table mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ngày SX</th>
                                    <th>Ca</th>
                                    <th>Mã NV</th>
                                    <th>Lệnh SX</th>
                                    <th>SL Đạt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProduction as $p)
                                    <tr>
                                        <td>{{ $p->ngay_sx->format('d/m/Y') }}</td>
                                        <td>{{ $p->ca }}</td>
                                        <td>{{ $p->ma_nv }}</td>
                                        <td>{{ $p->lenh_sx }}</td>
                                        <td>{{ number_format($p->sl_dat, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Warehouse --}}
            <div class="col-12">
                <div class="card-page">
                    <h6 class="fw-bold mb-3" style="color:#1e3a5f">
                        <i class="fa-solid fa-warehouse me-1"></i>Giao dịch kho gần đây
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover quick-table mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Loại</th>
                                    <th>Size</th>
                                    <th>Màu</th>
                                    <th>Số lượng</th>
                                    <th>Mã NV</th>
                                    <th>Lệnh SX</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentWarehouse as $w)
                                    <tr>
                                        <td>{{ $w->ngay->format('d/m/Y') }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $w->cong_doan == 'NHAPKHO' ? 'success' : 'danger' }}">{{ $w->cong_doan }}</span>
                                        </td>
                                        <td>{{ $w->size }}</td>
                                        <td>{{ $w->mau }}</td>
                                        <td>{{ number_format($w->so_luong, 2) }}</td>
                                        <td>{{ $w->ma_nv }}</td>
                                        <td>{{ $w->lenh_sx }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted text-center">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
