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
                <div class="col-auto">
                    <a href="{{ route('admin.orders.index', ['no_pl' => 1]) }}"
                        class="btn btn-sm {{ request('no_pl') ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="fa-solid fa-tag me-1"></i>Chưa có PL
                    </a>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal"
                        data-bs-target="#bulkSearchModal">
                        <i class="fa-solid fa-list-check me-1"></i>Tìm hàng loạt
                    </button>
                </div>
                @if (request('bulk'))
                    <div class="col-auto">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i>Xóa bộ lọc ({{ $data->total() }} kết quả)
                        </a>
                    </div>
                @endif
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>#</th>
                            <th>Job No</th>
                            <th>Fty PO</th>
                            <th>IM#</th>
                            <th>Color</th>
                            <th>YRD</th>
                            <th>Mã HH</th>
                            <th>Tên HH</th>
                            <th>PL Number</th>
                            <th>Lệnh SX</th>
                            <th>Status</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td><input type="checkbox" class="order-check" value="{{ $item->id }}"></td>
                                <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                <td class="fw-semibold">{{ $item->job_no }}</td>
                                <td>{{ $item->fty_po }}</td>
                                <td>{{ $item->im_number }}</td>
                                <td>{{ $item->color }}</td>
                                <td>{{ number_format($item->yrd, 2) }}</td>
                                <td>{{ $item->ma_hh }}</td>
                                <td>{{ $item->ten_hh }}</td>
                                <td>{{ $item->pl_number ?: '—' }}</td>
                                <td>{{ $item->lenh_sanxuat }}</td>
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
                                <td colspan="12" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($data->count())
                <div class="row mt-3 mb-3">
                    <div class="col-md-5">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-chart-pie me-1"></i>Tổng YRD theo Mã HH</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã HH</th>
                                    <th class="text-end">Số đơn</th>
                                    <th class="text-end">Tổng YRD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grouped = $data->getCollection()->groupBy('ma_hh');
                                    $grandTotal = 0;
                                @endphp
                                @foreach ($grouped as $maHh => $items)
                                    @php
                                        $sum = $items->sum('yrd');
                                        $grandTotal += $sum;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $maHh ?: '—' }}</td>
                                        <td class="text-end">{{ $items->count() }}</td>
                                        <td class="text-end">{{ number_format($sum, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-dark fw-bold">
                                    <td>Tổng cộng</td>
                                    <td class="text-end">{{ $data->count() }}</td>
                                    <td class="text-end">{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{ $data->links() }}
        </div>
    </div>

    <!-- Bulk Search Modal -->
    <div class="modal fade" id="bulkSearchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="GET" action="{{ route('admin.orders.index') }}">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-list-check me-2"></i>Tìm hàng loạt theo Fty PO</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Paste danh sách Fty PO (mỗi dòng 1 mã), hệ thống sẽ tìm tất cả
                            order khớp.</p>
                        <textarea name="bulk" class="form-control" rows="10"
                            placeholder="AU1902250-FOB&#10;AU1902274-FOB&#10;AY1902242-FOB&#10;...">{{ request('bulk') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fa-solid fa-search me-1"></i>Tìm
                        </button>
                    </div>
                </form>
            </div>
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
                            (bắt buộc). Tùy chọn: <strong>fty_po, im_number, color, qty, unit, ma_hh, yrd,
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

    {{-- ═══ FLOATING ACTION BAR ═══ --}}
    <div id="bulkBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-3 d-none" style="z-index:1050">
        <div class="bg-dark text-white rounded-pill px-4 py-2 shadow d-flex align-items-center gap-3">
            <span><strong id="selectedCount">0</strong> đơn hàng đã chọn</span>
            <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal"
                data-bs-target="#assignPlModal">
                <i class="fa-solid fa-tag me-1"></i>Gán PL Number
            </button>
        </div>
    </div>

    <!-- Assign PL Number Modal -->
    <div class="modal fade" id="assignPlModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="POST" action="{{ route('admin.orders.assign-pl') }}" id="assignPlForm">
                    @csrf
                    <div id="assignPlIds"></div>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-tag me-2"></i>Gán PL Number</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Nhập PL Number để gán cho <strong id="assignCount">0</strong> đơn
                            hàng đã chọn.</p>
                        <input type="text" name="pl_number" class="form-control" placeholder="Nhập PL Number..."
                            required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-check me-1"></i>Gán
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
                        <h5 class="modal-title"><i class="fa-solid fa-file-arrow-up me-2"></i>Import từ file khách hàng
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Dùng cho file Excel từ khách (South Island / TEXENCO format).</p>
                        <div class="alert alert-info small py-2 mb-3">
                            <strong>Mapping cột tự động:</strong><br>
                            JOB NO → Job No &bull; PTL → Fty PO &bull; IMe → IM# &bull; Clr → Color<br>
                            Odr Q → Qty &bull; Unit → Unit &bull; Fty POR → Mã HH &bull; Style# → Chart<br>
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

@section('scripts')
    <script>
        const checkAll = document.getElementById('checkAll');
        const bulkBar = document.getElementById('bulkBar');
        const selectedCount = document.getElementById('selectedCount');
        const assignCount = document.getElementById('assignCount');
        const assignPlIds = document.getElementById('assignPlIds');

        function getChecked() {
            return document.querySelectorAll('.order-check:checked');
        }

        function updateBar() {
            const checked = getChecked();
            const count = checked.length;
            selectedCount.textContent = count;
            assignCount.textContent = count;
            bulkBar.classList.toggle('d-none', count === 0);

            // rebuild hidden inputs
            assignPlIds.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'order_ids[]';
                inp.value = cb.value;
                assignPlIds.appendChild(inp);
            });
        }

        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.order-check').forEach(cb => cb.checked = this.checked);
            updateBar();
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('order-check')) updateBar();
        });
    </script>
@endsection
