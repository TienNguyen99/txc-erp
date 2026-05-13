@extends('layouts.app')
@section('css')
<style>
    .inventory-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }
    .inventory-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        font-weight: 600;
        padding: 0.4em 0.8em;
        border-radius: 6px;
    }
    .stock-level {
        font-size: 1.15rem;
        font-weight: 700;
    }
    .stock-positive {
        color: #198754;
    }
    .stock-negative {
        color: #dc3545;
    }
    .stock-zero {
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">
                <i class="fa-solid fa-boxes-stacked me-2"></i>Tổng Quan Tồn Kho
            </h4>
            <p class="text-muted mb-0">Hiển thị tồn kho hiện tại của từng mã hàng hóa, đang sản xuất và cần giao.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>Lịch sử giao dịch
            </a>
            <a href="{{ route('admin.warehouse-transactions.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-calendar-days me-1"></i>Chi tiết theo tháng
            </a>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="{{ route('admin.warehouse-transactions.ton-kho') }}" class="d-flex gap-2">
                <div class="input-group shadow-sm" style="max-width: 400px; border-radius: 6px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm mã hàng hóa..." value="{{ $search }}">
                    <button class="btn btn-primary px-4" type="submit">Tìm</button>
                </div>
                @if($search)
                    <a href="{{ route('admin.warehouse-transactions.ton-kho') }}" class="btn btn-light text-muted border shadow-sm">Xóa lọc</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-bottom-0">Mã Hàng Hóa</th>
                            <th class="py-3 border-bottom-0">Phân Loại (Size/Màu)</th>
                            <th class="py-3 text-center border-bottom-0 text-success"><i class="fa-solid fa-arrow-down me-1"></i>Tổng Nhập</th>
                            <th class="py-3 text-center border-bottom-0 text-danger"><i class="fa-solid fa-arrow-up me-1"></i>Tổng Xuất</th>
                            <th class="py-3 text-center border-bottom-0">Đang Sản Xuất</th>
                            <th class="py-3 text-center border-bottom-0">Cần Giao</th>
                            <th class="pe-4 py-3 text-end border-bottom-0">Tồn Hiện Tại</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($inventory as $item)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                                        <i class="fa-solid fa-box-open text-primary" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="color: #1e3a5f;">{{ $item->ma_hh }}</h6>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">{{ $item->ten_hh }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->size || $item->mau)
                                    @if($item->size)
                                    <span class="badge bg-secondary rounded-pill me-1"><i class="fa-solid fa-ruler-combined me-1"></i>{{ $item->size }}</span>
                                    @endif
                                    @if($item->mau)
                                    <span class="badge bg-info text-dark rounded-pill"><i class="fa-solid fa-palette me-1"></i>{{ $item->mau }}</span>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">Mặc định</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold text-success">{{ number_format($item->tong_nhap, 2) }}</td>
                            <td class="text-center fw-semibold text-danger">{{ number_format($item->tong_xuat, 2) }}</td>
                            <td class="text-center">
                                @if($item->dang_sx > 0)
                                    <span class="badge bg-warning text-dark status-badge rounded-pill"><i class="fa-solid fa-industry me-1"></i>{{ number_format($item->dang_sx, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->can_di > 0)
                                    <span class="badge bg-primary status-badge rounded-pill"><i class="fa-solid fa-truck-fast me-1"></i>{{ number_format($item->can_di, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                @php
                                    $stockClass = 'stock-zero';
                                    if ($item->ton_hien_tai > 0) $stockClass = 'stock-positive';
                                    elseif ($item->ton_hien_tai < 0) $stockClass = 'stock-negative';
                                @endphp
                                <div class="stock-level {{ $stockClass }} bg-light rounded px-3 py-2 d-inline-block shadow-sm">
                                    {{ number_format($item->ton_hien_tai, 2) }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-box-open mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
                                    <h5>Không tìm thấy dữ liệu tồn kho</h5>
                                    <p>Thử thay đổi từ khóa tìm kiếm hoặc kiểm tra lại các giao dịch.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($inventory->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $inventory->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
