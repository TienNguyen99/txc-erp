@extends('layouts.app')

@section('page-title', 'Phiếu nhập/xuất kho')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.warehouse-transactions.index') }}" class="text-decoration-none"
                    style="font-size:.85rem;color:var(--primary);font-weight:500">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại kho
                </a>
                <h4 class="page-title mt-2 mb-0">
                    <i class="fa-solid fa-file-invoice me-2"></i>Phiếu nhập/xuất kho
                </h4>
            </div>
            <a href="{{ route('admin.warehouse-transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Tạo giao dịch kho
            </a>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tất cả loại phiếu</option>
                        <option value="NHAPKHO" @selected(request('type') === 'NHAPKHO')>Phiếu nhập kho</option>
                        <option value="XUATKHO" @selected(request('type') === 'XUATKHO')>Phiếu xuất kho</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm số phiếu / ghi chú..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-search me-1"></i>Tìm
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Số phiếu</th>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th class="text-end">Số dòng</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Ghi chú</th>
                            <th class="text-center no-sort">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td class="fw-semibold">{{ $document->document_no }}</td>
                                <td>{{ $document->document_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $document->type === 'NHAPKHO' ? 'success' : 'danger' }}">
                                        {{ $document->type === 'NHAPKHO' ? 'Nhập kho' : 'Xuất kho' }}
                                    </span>
                                </td>
                                <td class="text-end">{{ $document->items_count }}</td>
                                <td>{{ $document->createdBy?->name ?: '-' }}</td>
                                <td>{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ Str::limit($document->note, 50) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.warehouse-documents.show', $document) }}" class="btn btn-info btn-xs" title="Xem phiếu">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.warehouse-documents.print', $document) }}" target="_blank" class="btn btn-primary btn-xs" title="In phiếu">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Chưa có phiếu kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $documents->links() }}
        </div>
    </div>
@endsection
