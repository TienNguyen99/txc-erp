@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-truck-fast me-2"></i>Quản lý Order Tracking</h4>
            <a href="{{ route('admin.order-tracking.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Tracking
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')

            {{-- ═══ BỘ LỌC THEO PL NUMBER / CHART ═══ --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-0" style="font-size:.8rem">PL Number <small class="text-muted">(chọn
                            nhiều)</small></label>
                    <select name="pl_number[]" class="form-select form-select-sm" multiple size="3">
                        @foreach ($plNumbers as $pl)
                            <option value="{{ $pl }}"
                                {{ in_array($pl, (array) request('pl_number', [])) ? 'selected' : '' }}>
                                {{ $pl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0" style="font-size:.8rem">Chart</label>
                    <select name="chart" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        @foreach ($charts as $c)
                            <option value="{{ $c }}" {{ request('chart') == $c ? 'selected' : '' }}>
                                {{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" style="font-size:.8rem">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm PL Number / Màu..." value="{{ request('search') }}">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Lọc</button>
                    <a href="{{ route('admin.order-tracking.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset
                    </a>
                </div>
            </form>

            {{-- ═══ BẢNG TỔNG HỢP MÃ HH (chỉ hiển thị khi có filter) ═══ --}}
            @if ($hasFilter && $summary->count())
                @php
                    $duHang = $summary->where('du_hang', true);
                    $thieuHang = $summary->where('du_hang', false);
                @endphp

                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-chart-bar me-1"></i>Tổng hợp Mã HH
                        @if (request('pl_number'))
                            — PL: <span class="text-dark">{{ implode(', ', (array) request('pl_number')) }}</span>
                        @endif
                        @if (request('chart'))
                            — Chart: <span class="text-dark">{{ request('chart') }}</span>
                        @endif
                    </h6>

                    {{-- ═══ NHÓM 1: ĐỦ HÀNG — SẴN SÀNG GIAO ═══ --}}
                    @if ($duHang->count())
                        <div class="card border-success mb-3">
                            <div class="card-header bg-success text-white py-2">
                                <i class="fa-solid fa-check-circle me-1"></i>
                                <strong>Đủ hàng — Sẵn sàng xuất kho giao</strong>
                                <span class="badge bg-light text-success ms-2">{{ $duHang->count() }} mã</span>
                            </div>
                            <div class="card-body p-2">
                                <form method="POST" action="{{ route('admin.order-tracking.ship-from-stock') }}"
                                    id="shipForm">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-2">
                                            <thead class="table-success">
                                                <tr>
                                                    <th><input type="checkbox" id="checkAllShip" checked></th>
                                                    <th>Mã HH</th>
                                                    <th class="text-center">Số đơn</th>
                                                    <th class="text-end">Cần giao</th>
                                                    <th class="text-end">Tồn kho</th>
                                                    <th class="text-end">Dư</th>
                                                    <th>Công đoạn SX</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($duHang as $row)
                                                    <tr>
                                                        <td>
                                                            @foreach ($row->order_ids as $oid)
                                                                <input type="checkbox" name="order_ids[]"
                                                                    value="{{ $oid }}" class="ship-check" checked
                                                                    style="display:none">
                                                            @endforeach
                                                            <input type="checkbox" class="ship-group-check" checked>
                                                        </td>
                                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                                        <td class="text-center">{{ $row->so_don }}</td>
                                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                                        <td class="text-end fw-bold text-success">
                                                            {{ number_format($row->ton_kho, 2) }}</td>
                                                        <td class="text-end text-success">
                                                            +{{ number_format($row->ton_kho - $row->tong_qty, 2) }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-1"
                                                                style="font-size:.75rem">
                                                                @foreach ($stages as $stageName => $stageInfo)
                                                                    @php $stageQty = $row->stage_breakdown[$stageName] ?? 0; @endphp
                                                                    @if ($stageQty > 0)
                                                                        <span class="badge bg-{{ $stageInfo['color'] }}">
                                                                            <i
                                                                                class="fa-solid {{ $stageInfo['icon'] }} me-1"></i>{{ number_format($stageQty, 0) }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                                @if ($row->stage_breakdown->sum() == 0)
                                                                    <span class="text-muted">Chưa có tracking</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm" id="btnShipFromStock"
                                        onclick="return confirm('Xuất kho giao hàng cho các đơn đã chọn?')">
                                        <i class="fa-solid fa-truck-fast me-1"></i>Xuất kho giao hàng
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- ═══ NHÓM 2: THIẾU HÀNG — CẦN SẢN XUẤT ═══ --}}
                    @if ($thieuHang->count())
                        <div class="card border-danger mb-3">
                            <div class="card-header bg-danger text-white py-2">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <strong>Thiếu hàng — Cần sản xuất thêm</strong>
                                <span class="badge bg-light text-danger ms-2">{{ $thieuHang->count() }} mã</span>
                            </div>
                            <div class="card-body p-2">
                                <form method="POST" action="{{ route('admin.order-tracking.generate') }}"
                                    id="generateForm">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-2">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th><input type="checkbox" id="checkAllGenerate" checked></th>
                                                    <th>Mã HH</th>
                                                    <th class="text-center">Số đơn</th>
                                                    <th class="text-end">Cần giao</th>
                                                    <th class="text-end">Tồn kho</th>
                                                    <th class="text-end">Thiếu</th>
                                                    <th>Công đoạn SX</th>
                                                    <th>Tiến độ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($thieuHang as $row)
                                                    <tr>
                                                        <td>
                                                            @foreach ($row->order_ids as $oid)
                                                                <input type="checkbox" name="order_ids[]"
                                                                    value="{{ $oid }}" class="generate-check"
                                                                    checked style="display:none">
                                                            @endforeach
                                                            <input type="checkbox" class="generate-group-check" checked>
                                                        </td>
                                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                                        <td class="text-center">{{ $row->so_don }}</td>
                                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                                        <td
                                                            class="text-end {{ $row->ton_kho > 0 ? 'text-success' : 'text-muted' }}">
                                                            {{ number_format($row->ton_kho, 2) }}
                                                        </td>
                                                        <td class="text-end fw-bold text-danger">
                                                            {{ number_format($row->thieu, 2) }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-1"
                                                                style="font-size:.75rem">
                                                                @foreach ($stages as $stageName => $stageInfo)
                                                                    @php $stageQty = $row->stage_breakdown[$stageName] ?? 0; @endphp
                                                                    @if ($stageQty > 0)
                                                                        <span class="badge bg-{{ $stageInfo['color'] }}">
                                                                            <i
                                                                                class="fa-solid {{ $stageInfo['icon'] }} me-1"></i>{{ number_format($stageQty, 0) }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                                @if ($row->stage_breakdown->sum() == 0)
                                                                    <span class="text-muted">Chưa có tracking</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td style="min-width:120px">
                                                            @php
                                                                $pct =
                                                                    $row->tong_qty > 0
                                                                        ? min(
                                                                            100,
                                                                            round(
                                                                                ($row->sl_warehouse / $row->tong_qty) *
                                                                                    100,
                                                                            ),
                                                                        )
                                                                        : 0;
                                                                $barColor = $pct >= 50 ? 'bg-info' : 'bg-warning';
                                                            @endphp
                                                            <div class="progress" style="height:18px">
                                                                <div class="progress-bar {{ $barColor }}"
                                                                    style="width:{{ $pct }}%">
                                                                    {{ $pct }}%</div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Tạo Tracking & Lên lệnh SX
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @elseif ($hasFilter && $summary->isEmpty())
                <div class="alert alert-info">Không tìm thấy đơn hàng nào với bộ lọc đã chọn.</div>
            @endif

            {{-- ═══ DANH SÁCH TRACKING ═══ --}}
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-list me-1"></i>Danh sách Tracking</h6>
            <form id="trackingActionForm" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="checkAllTracking"></th>
                                <th>#</th>
                                <th>Order (Job No)</th>
                                <th>PL Number</th>
                                <th>Mã HH</th>
                                <th>Màu</th>
                                <th>Kích</th>
                                <th>Công đoạn</th>
                                <th class="text-end">SL Đơn hàng</th>
                                <th class="text-end">SL Sản xuất</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td><input type="checkbox" name="tracking_ids[]" value="{{ $item->id }}"
                                            class="tracking-check"></td>
                                    <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                    <td class="fw-semibold">{{ $item->order->job_no ?? '—' }}</td>
                                    <td>{{ $item->pl_number }}</td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->mau }}</td>
                                    <td>{{ $item->kich }}</td>
                                    <td>
                                        @php
                                            $stageInfo = $stages[$item->cong_doan] ?? [
                                                'icon' => 'fa-question',
                                                'color' => 'secondary',
                                                'order' => -1,
                                            ];
                                            $currentOrder = $stageInfo['order'];
                                        @endphp
                                        <div class="d-flex align-items-center gap-1">
                                            @foreach ($stages as $stageName => $info)
                                                @php
                                                    $isDone = $info['order'] < $currentOrder;
                                                    $isCurrent = $stageName === $item->cong_doan;
                                                @endphp
                                                <span
                                                    class="rounded-circle d-inline-flex align-items-center justify-content-center
                                                    {{ $isCurrent ? 'bg-' . $info['color'] . ' text-white' : ($isDone ? 'bg-' . $info['color'] . ' text-white opacity-50' : 'bg-light text-muted border') }}"
                                                    style="width:24px;height:24px;font-size:.6rem"
                                                    title="{{ $stageName }}">
                                                    <i class="fa-solid {{ $info['icon'] }}"></i>
                                                </span>
                                                @if (!$loop->last)
                                                    <i class="fa-solid fa-chevron-right"
                                                        style="font-size:.45rem;color:#ccc"></i>
                                                @endif
                                            @endforeach
                                            <span class="badge bg-{{ $stageInfo['color'] }} ms-1"
                                                style="font-size:.7rem">{{ $item->cong_doan }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($item->sl_don_hang, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->sl_san_xuat, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.order-tracking.edit', $item) }}"
                                            class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                        <button type="button" class="btn btn-danger btn-xs btn-delete-tracking"
                                            data-url="{{ route('admin.order-tracking.destroy', $item) }}"
                                            onclick="if(confirm('Xóa tracking này?')){document.getElementById('deleteForm').action=this.dataset.url;document.getElementById('deleteForm').submit();}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-muted text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if ($data->count())
                <div class="row mt-3 mb-3">
                    <div class="col-md-5">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-chart-pie me-1"></i>Tổng YRD theo Mã HH</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã HH</th>
                                    <th class="text-end">Số tracking</th>
                                    <th class="text-end">Tổng SL Đơn hàng</th>
                                    <th class="text-end">Tổng SL Sản xuất</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grouped = $data->getCollection()->groupBy('size');
                                    $grandDH = 0;
                                    $grandSX = 0;
                                @endphp
                                @foreach ($grouped as $maHh => $items)
                                    @php
                                        $sumDH = $items->sum('sl_don_hang');
                                        $sumSX = $items->sum('sl_san_xuat');
                                        $grandDH += $sumDH;
                                        $grandSX += $sumSX;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $maHh ?: '—' }}</td>
                                        <td class="text-end">{{ $items->count() }}</td>
                                        <td class="text-end">{{ number_format($sumDH, 2) }}</td>
                                        <td class="text-end">{{ number_format($sumSX, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-dark fw-bold">
                                    <td>Tổng cộng</td>
                                    <td class="text-end">{{ $data->count() }}</td>
                                    <td class="text-end">{{ number_format($grandDH, 2) }}</td>
                                    <td class="text-end">{{ number_format($grandSX, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Nút chuyển hàng loạt --}}
            @if ($data->count())
                <div class="d-flex gap-2 mt-2 mb-3">
                    <button type="button" class="btn btn-info btn-sm text-white" id="btnPushProduction">
                        <i class="fa-solid fa-industry me-1"></i>Chuyển sang Sản xuất
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btnPushWarehouse">
                        <i class="fa-solid fa-warehouse me-1"></i>Nhập Kho
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnBulkDelete">
                        <i class="fa-solid fa-trash me-1"></i>Xóa hàng loạt
                    </button>
                </div>
            @endif

            {{ $data->links() }}
        </div>
    </div>

    {{-- Hidden delete form (outside trackingActionForm to avoid nesting) --}}
    <form id="deleteForm" method="POST" style="display:none">@csrf @method('DELETE')</form>

    <script>
        // Check all — Xuất kho giao hàng
        document.getElementById('checkAllShip')?.addEventListener('change', function() {
            document.querySelectorAll('.ship-group-check').forEach(cb => cb.checked = this.checked);
            document.querySelectorAll('.ship-check').forEach(cb => cb.checked = this.checked);
        });
        document.querySelectorAll('.ship-group-check').forEach(cb => {
            cb.addEventListener('change', function() {
                const row = this.closest('tr');
                row.querySelectorAll('.ship-check').forEach(h => h.checked = this.checked);
            });
        });

        // Check all — Lên lệnh SX
        document.getElementById('checkAllGenerate')?.addEventListener('change', function() {
            document.querySelectorAll('.generate-group-check').forEach(cb => cb.checked = this.checked);
            document.querySelectorAll('.generate-check').forEach(cb => cb.checked = this.checked);
        });
        document.querySelectorAll('.generate-group-check').forEach(cb => {
            cb.addEventListener('change', function() {
                const row = this.closest('tr');
                row.querySelectorAll('.generate-check').forEach(h => h.checked = this.checked);
            });
        });

        // Check all tracking
        document.getElementById('checkAllTracking')?.addEventListener('change', function() {
            document.querySelectorAll('.tracking-check').forEach(cb => cb.checked = this.checked);
        });

        // Push to Production
        document.getElementById('btnPushProduction')?.addEventListener('click', function() {
            const form = document.getElementById('trackingActionForm');
            const checked = form.querySelectorAll('.tracking-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 tracking.');
            if (!confirm(`Chuyển ${checked.length} mục sang sản xuất?`)) return;
            form.action = '{{ route('admin.order-tracking.push-production') }}';
            form.submit();
        });

        // Push to Warehouse
        document.getElementById('btnPushWarehouse')?.addEventListener('click', function() {
            const form = document.getElementById('trackingActionForm');
            const checked = form.querySelectorAll('.tracking-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 tracking.');
            if (!confirm(`Nhập kho ${checked.length} mục?`)) return;
            form.action = '{{ route('admin.order-tracking.push-warehouse') }}';
            form.submit();
        });

        // Bulk Delete
        document.getElementById('btnBulkDelete')?.addEventListener('click', function() {
            const form = document.getElementById('trackingActionForm');
            const checked = form.querySelectorAll('.tracking-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 tracking.');
            if (!confirm(`Xóa ${checked.length} tracking đã chọn?`)) return;
            form.action = '{{ route('admin.order-tracking.bulk-delete') }}';
            form.submit();
        });
    </script>
@endsection
