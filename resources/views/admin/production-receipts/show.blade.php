@extends('layouts.app')

@section('page-title', 'Phiếu nhập kho')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.production-reports.index') }}" class="text-decoration-none"
                    style="font-size:.85rem;color:var(--primary);font-weight:500">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại báo cáo sản xuất
                </a>
                <h4 class="page-title mt-2 mb-0"><i class="fa-solid fa-file-invoice me-2"></i>{{ $productionReceipt->receipt_no }}</h4>
            </div>
            <a href="{{ route('admin.production-receipts.print', $productionReceipt) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-print me-1"></i>In phiếu
            </a>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="text-muted small">Ngày phiếu</div>
                    <div class="fw-semibold">{{ $productionReceipt->receipt_date?->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Công đoạn</div>
                    <div class="fw-semibold">{{ $productionReceipt->cong_doan ?: '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Người tạo</div>
                    <div class="fw-semibold">{{ $productionReceipt->postedBy?->name ?: '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Trạng thái</div>
                    <span class="badge bg-primary">Đã tạo phiếu</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Tên sản phẩm</th>
                            <th>Mã hàng</th>
                            <th>Màu sắc</th>
                            <th>Size</th>
                            <th class="text-end">Số lượng</th>
                            <th>ĐVT</th>
                            <th>Lệnh</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productionReceipt->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->ten_san_pham }}</td>
                                <td>{{ $item->ma_hh }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ $item->size }}</td>
                                <td class="text-end">{{ number_format((float) $item->so_luong, 2) }}</td>
                                <td>{{ $item->don_vi }}</td>
                                <td>{{ $item->lenh_sx }}</td>
                                <td>{{ $item->ghi_chu }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
