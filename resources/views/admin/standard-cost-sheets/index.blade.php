@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="page-title mb-1"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Định mức giá vốn</h4>
                <div class="text-muted small">Lưu công thức giá vốn theo mã hàng, phiên bản và ngày hiệu lực.</div>
            </div>
            <a href="{{ route('admin.costing.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chart-column me-1"></i>Giá vốn thực tế theo tháng
            </a>
        </div>

        @include('admin.partials.alert')

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card-page p-0">
                    <div class="p-3 border-bottom">
                        <form method="GET" class="d-flex gap-2">
                            <input name="search" value="{{ $search }}" class="form-control form-control-sm"
                                placeholder="Tìm mã hàng, tên hàng hoặc phiên bản...">
                            <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Mã hàng</th><th>Phiên bản</th><th>Hiệu lực</th><th>Trạng thái</th><th class="text-end">Dòng chi phí</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($sheets as $sheet)
                                    <tr>
                                        <td><div class="fw-bold">{{ $sheet->product->ma_hh }}</div><small class="text-muted">{{ $sheet->product->ten_hh }}</small></td>
                                        <td>{{ $sheet->version }}</td>
                                        <td>{{ $sheet->effective_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge {{ $sheet->status === 'active' ? 'bg-success' : ($sheet->status === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                {{ \App\Models\StandardCostSheet::STATUSES[$sheet->status] ?? $sheet->status }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($sheet->lines_count) }}</td>
                                        <td class="text-end"><a href="{{ route('admin.standard-cost-sheets.show', $sheet) }}" class="btn btn-outline-primary btn-sm">Mở</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có bảng giá vốn định mức.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($sheets->hasPages())
                        <div class="p-3 border-top">{{ $sheets->links() }}</div>
                    @endif
                </div>
            </div>
            <div class="col-xl-4">
                @can('warehouse.edit')
                    <div class="card-page">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus text-primary me-2"></i>Tạo bảng giá vốn</h5>
                        <form method="POST" action="{{ route('admin.standard-cost-sheets.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Mã thành phẩm <span class="text-danger">*</span></label>
                                <select name="product_id" id="cost-product" class="form-select" required>
                                    <option value="">Chọn mã hàng...</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->ma_hh }} - {{ $product->ten_hh }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Phiên bản <span class="text-danger">*</span></label>
                                    <input name="version" value="{{ old('version', now()->format('Ymd').'-V1') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ngày hiệu lực <span class="text-danger">*</span></label>
                                    <input type="date" name="effective_date" value="{{ old('effective_date', now()->toDateString()) }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Sản lượng chuẩn</label>
                                    <input type="number" step="0.0001" min="0.0001" name="standard_output_qty" value="{{ old('standard_output_qty', 1) }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giá bán dự kiến</label>
                                    <input type="number" step="0.01" min="0" name="sale_price_vnd" value="{{ old('sale_price_vnd', 0) }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Biên lợi nhuận mục tiêu %</label>
                                    <input type="number" step="0.01" min="0" max="95" name="target_margin_pct" value="{{ old('target_margin_pct', 30) }}" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fa-solid fa-arrow-right me-1"></i>Tạo và cấu hình</button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('cost-product')) new TomSelect('#cost-product', { create: false });
        });
    </script>
@endsection
