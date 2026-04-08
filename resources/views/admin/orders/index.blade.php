@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-file-invoice me-2"></i>Quản lý Đơn hàng</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.orders.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-lines me-1"></i>Tải Template
                </a>
                <a href="{{ route('admin.orders.export') }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                </a>
                <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i>Import Excel
                </button>
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                    data-bs-target="#importCustomerModal">
                    <i class="fa-solid fa-file-arrow-up me-1"></i>Import từ khách
                </button>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Thêm Đơn hàng
                </a>
            </div>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Job No / Fty PO..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Trạng thái --</option>
                        @foreach (['pending', 'in_production', 'done', 'shipped'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Job No</th>
                            <th>Fty PO</th>
                            <th>IM#</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td class="fw-semibold">{{ $item->job_no }}</td>
                                <td>{{ $item->fty_po }}</td>
                                <td>{{ $item->im_number }}</td>
                                <td>{{ $item->color }}</td>
                                <td>{{ number_format($item->qty, 2) }}</td>
                                <td>{{ $item->size }}</td>
                                <td>
                                    @php $colors = ['pending'=>'warning','in_production'=>'info','done'=>'success','shipped'=>'primary']; @endphp
                                    <span
                                        class="badge bg-{{ $colors[$item->status] ?? 'secondary' }}">{{ $item->status }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.edit', $item) }}" class="btn btn-warning btn-xs"><i
                                            class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.orders.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa đơn hàng này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="POST" action="{{ route('admin.orders.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-file-import me-2"></i>Import Đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">File Excel cần có cột header: <strong>job_no</strong>
                            (bắt buộc). Tùy chọn: <strong>fty_po, im_number, color, qty, unit, size, yrd,
                                can_giao_1, can_giao_2, pl_number, tagtime_etc, sig_need_date, chart,
                                price_usd_auto, price_usd, to_khai, status</strong>. Bấm "Tải Template" để lấy
                            file mẫu.</p>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fa-solid fa-upload me-1"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Customer Modal -->
    <div class="modal fade" id="importCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="POST" action="{{ route('admin.orders.import-customer') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fa-solid fa-file-arrow-up me-2"></i>Import từ file khách hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Dùng cho file Excel từ khách (South Island / TEXENCO format).</p>
                        <div class="alert alert-info small py-2 mb-3">
                            <strong>Mapping cột tự động:</strong><br>
                            JOB NO → Job No &bull; PTL → Fty PO &bull; IMe → IM# &bull; Clr → Color<br>
                            Odr Q → Qty &bull; Unit → Unit &bull; Fty POR → Size &bull; Style# → Chart<br>
                            PO Place Date → Sig Need Date &bull; RMDS OETC → Tagtime ETC<br>
                            <em>Nếu trùng JOB NO sẽ cập nhật, không tạo mới.</em>
                        </div>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-upload me-1"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
