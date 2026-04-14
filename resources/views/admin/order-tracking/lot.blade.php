@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.order-tracking.index') }}" class="text-decoration-none"
                    style="font-size:.85rem;color:var(--primary);font-weight:500">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
                </a>
                <h4 class="page-title mt-2 mb-0">
                    <i class="fa-solid fa-layer-group me-2"></i>Order Tracking — <span
                        class="text-primary">{{ $trackingNumber }}</span>
                </h4>
                <div class="mt-1" style="font-size:.85rem">
                    <span class="text-muted">PL Numbers:</span>
                    @foreach ($plNumbersInLot as $pl)
                        <span class="badge bg-secondary">{{ $pl }}</span>
                    @endforeach
                </div>
            </div>
            {{-- Chuyển OT nhanh --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="font-size:.8rem">Chuyển OT:</label>
                <select class="form-select form-select-sm" style="width:220px" id="switchOT"
                    onchange="if(this.value) window.location.href=this.value">
                    <option value="">-- Chọn OT Number --</option>
                    @foreach ($allTrackingNumbers as $tn)
                        <option value="{{ route('admin.order-tracking.lot', $tn) }}"
                            {{ $tn === $trackingNumber ? 'selected' : '' }}>
                            {{ $tn }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            {{-- ═══ THỐNG KÊ TỔNG ═══ --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Tổng Mã HH</div>
                            <h3 class="fw-bold text-primary mb-0">{{ $stats->total_mahh }}</h3>
                            <small class="text-muted">mã hàng hóa</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Cần giao / Tồn kho</div>
                            <h3 class="fw-bold mb-0">
                                <span class="text-dark">{{ number_format($stats->tong_can_giao, 0) }}</span>
                                <span class="text-muted mx-1">/</span>
                                <span class="text-success">{{ number_format($stats->tong_ton_kho, 0) }}</span>
                            </h3>
                            <small class="text-muted">YRD</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Đang sản xuất</div>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($stats->tong_dang_sx, 0) }}</h3>
                            <small class="text-muted">{{ $stats->dang_sx }} mã đang SX</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div
                        class="card border-0 shadow-sm h-100 {{ $stats->tong_thieu > 0 ? 'border-start border-danger border-3' : 'border-start border-success border-3' }}">
                        <div class="card-body text-center">
                            @if ($stats->tong_thieu > 0)
                                <div class="text-muted" style="font-size:.75rem">Còn thiếu</div>
                                <h3 class="fw-bold text-danger mb-0">{{ number_format($stats->tong_thieu, 0) }}</h3>
                                <small class="text-danger">{{ $stats->thieu_hang }} mã thiếu hàng</small>
                            @else
                                <div class="text-muted" style="font-size:.75rem">Trạng thái</div>
                                <h3 class="fw-bold text-success mb-0"><i class="fa-solid fa-check-circle"></i></h3>
                                <small class="text-success">Đủ hàng tất cả</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ BẢNG TIẾN ĐỘ THEO MÃ HH ═══ --}}
            @if ($summary->count())
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-primary mb-0">
                            <i class="fa-solid fa-chart-bar me-1"></i>Tiến độ sản xuất theo Mã HH
                            <span class="badge bg-dark ms-1">Lệnh tổng: {{ $trackingNumber }}</span>
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalBatchLenhSx">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Tạo lệnh SX
                            </button>
                            <a href="{{ route('admin.order-tracking.export-lenh-sx', $trackingNumber) }}"
                                class="btn btn-success btn-sm">
                                <i class="fa-solid fa-file-excel me-1"></i>In lệnh SX (Excel)
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">STT</th>
                                    <th>Lệnh SX</th>
                                    <th>Mã HH</th>
                                    <th class="text-center">Số PO</th>
                                    <th class="text-end">Cần giao</th>
                                    <th class="text-end">Đang SX</th>
                                    <th class="text-end">Tồn kho</th>
                                    <th class="text-end">Thiếu / Dư</th>
                                    <th style="min-width:200px">Tiến độ</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary->sortBy('stt') as $row)
                                    @php
                                        $diff = $row->ton_kho - $row->tong_qty;
                                        $pctKho =
                                            $row->tong_qty > 0
                                                ? min(100, round(($row->ton_kho / $row->tong_qty) * 100))
                                                : 0;
                                        $pctSx =
                                            $row->tong_qty > 0
                                                ? min(
                                                    100 - $pctKho,
                                                    round(($row->sl_production / $row->tong_qty) * 100),
                                                )
                                                : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold">{{ $row->stt }}</td>
                                        <td>
                                            <span class="badge bg-primary" style="font-size:.8rem">{{ $row->lenh_sx }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $row->ma_hh ?: '—' }}</td>
                                        <td class="text-center">{{ $row->so_don }}</td>
                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                        <td
                                            class="text-end {{ $row->sl_production > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->sl_production, 2) }}
                                        </td>
                                        <td
                                            class="text-end {{ $row->ton_kho > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->ton_kho, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $diff >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                        </td>
                                        <td>
                                            <div class="progress" style="height:20px">
                                                @if ($pctKho > 0)
                                                    <div class="progress-bar bg-success" style="width:{{ $pctKho }}%"
                                                        title="Tồn kho: {{ number_format($row->ton_kho, 0) }}">
                                                        @if ($pctKho >= 15)
                                                            {{ $pctKho }}%
                                                        @endif
                                                    </div>
                                                @endif
                                                @if ($pctSx > 0)
                                                    <div class="progress-bar bg-info progress-bar-striped progress-bar-animated"
                                                        style="width:{{ $pctSx }}%"
                                                        title="Đang SX: {{ number_format($row->sl_production, 0) }}">
                                                        @if ($pctSx >= 15)
                                                            {{ $pctSx }}%
                                                        @endif
                                                    </div>
                                                @endif
                                                @if ($pctKho == 0 && $pctSx == 0)
                                                    <div class="progress-bar bg-light text-dark" style="width:100%">Chưa SX
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="d-flex justify-content-between"
                                                style="font-size:.65rem;margin-top:2px">
                                                <span class="text-success"><i
                                                        class="fa-solid fa-warehouse me-1"></i>Kho</span>
                                                <span class="text-info"><i class="fa-solid fa-industry me-1"></i>SX</span>
                                                <span>{{ $row->total_progress }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($row->du_hang)
                                                <span class="badge bg-success"><i
                                                        class="fa-solid fa-check-circle me-1"></i>Đủ hàng</span>
                                            @elseif ($row->sl_production > 0)
                                                <span class="badge bg-info"><i class="fa-solid fa-industry me-1"></i>Đang
                                                    SX</span>
                                            @elseif ($row->stage_breakdown->sum() > 0)
                                                <span class="badge bg-warning text-dark"><i
                                                        class="fa-solid fa-clock me-1"></i>Chờ SX</span>
                                            @else
                                                <span class="badge bg-danger"><i
                                                        class="fa-solid fa-triangle-exclamation me-1"></i>Thiếu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark fw-bold">
                                <tr>
                                    <td colspan="3">Tổng ({{ $summary->count() }} mã)</td>
                                    <td class="text-center">{{ $summary->sum('so_don') }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('tong_qty'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('sl_production'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('ton_kho'), 2) }}</td>
                                    <td class="text-end">
                                        {{ number_format($summary->sum('ton_kho') - $summary->sum('tong_qty'), 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- ═══ MODAL TẠO LỆNH SX BATCH ═══ --}}
                <div class="modal fade" id="modalBatchLenhSx" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.order-tracking.create-production-batch') }}">
                                @csrf
                                <input type="hidden" name="tracking_number" value="{{ $trackingNumber }}">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="fa-solid fa-industry me-2"></i>Tạo lệnh sản xuất — {{ $trackingNumber }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- Thông tin chung --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Công đoạn</label>
                                            <select name="cong_doan" class="form-select" required>
                                                <option value="Dệt">Dệt</option>
                                                <option value="Định hình">Định hình</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ngày SX</label>
                                            <input type="date" name="ngay_sx" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Ca</label>
                                            <select name="ca" class="form-select">
                                                <option value="1">Ca 1</option>
                                                <option value="2">Ca 2</option>
                                                <option value="3">Ca 3</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">% Hao hụt</label>
                                            <input type="number" step="0.1" min="0" max="100" name="pct_hao_hut"
                                                class="form-control" value="10" id="pctHaoHut">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnApplyHaoHut">
                                                <i class="fa-solid fa-calculator me-1"></i>Áp dụng %
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Bảng chọn mã HH --}}
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0" id="tblBatchSx">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th class="text-center" style="width:40px">
                                                        <input type="checkbox" id="checkAllBatch" checked>
                                                    </th>
                                                    <th class="text-center">STT</th>
                                                    <th>Lệnh SX</th>
                                                    <th>Mã HH</th>
                                                    <th class="text-end">SL Cần giao</th>
                                                    <th class="text-end">Tồn kho</th>
                                                    <th class="text-end">Thiếu</th>
                                                    <th class="text-end" style="min-width:120px">SL Sản xuất</th>
                                                    <th class="text-end" style="min-width:120px">SL + %HH</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($summary->sortBy('stt') as $row)
                                                    @php $thieu = max(0, $row->tong_qty - $row->ton_kho); @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="items[{{ $loop->index }}][selected]"
                                                                value="1" class="batch-check" checked>
                                                        </td>
                                                        <td class="text-center fw-bold">{{ $row->stt }}</td>
                                                        <td>
                                                            <input type="hidden" name="items[{{ $loop->index }}][lenh_sx]"
                                                                value="{{ $row->lenh_sx }}">
                                                            <span class="badge bg-primary">{{ $row->lenh_sx }}</span>
                                                        </td>
                                                        <td class="fw-semibold">
                                                            <input type="hidden" name="items[{{ $loop->index }}][ma_hh]"
                                                                value="{{ $row->ma_hh }}">
                                                            {{ $row->ma_hh }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                                        <td class="text-end text-success">{{ number_format($row->ton_kho, 2) }}</td>
                                                        <td class="text-end {{ $thieu > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                            {{ number_format($thieu, 2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <input type="number" step="0.01" min="0"
                                                                name="items[{{ $loop->index }}][sl_dat]"
                                                                value="{{ $row->tong_qty }}"
                                                                class="form-control form-control-sm text-end sl-dat-input"
                                                                data-base="{{ $row->tong_qty }}">
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="sl-plus-hh fw-bold text-primary">{{ number_format($row->tong_qty * 1.10, 2) }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-check me-1"></i>Tạo lệnh SX ({{ $summary->count() }} mã)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ═══ DANH SÁCH ĐƠN HÀNG TRONG LÔ ═══ --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="fa-solid fa-file-invoice me-1"></i>Đơn hàng trong lô
                    <span class="badge bg-primary ms-1">{{ $orders->count() }}</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Job No</th>
                                <th>Fty PO</th>
                                <th>Mã HH</th>
                                <th>Màu</th>
                                <th class="text-end">QTY</th>
                                <th class="text-end">YRD</th>
                                <th>Chart</th>
                                <th>Lệnh SX</th>
                                <th>Ngày giao</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'Chờ xử lý', 'color' => 'warning'],
                                        'in_production' => ['label' => 'Đang SX', 'color' => 'info'],
                                        'done' => ['label' => 'Hoàn thành', 'color' => 'success'],
                                        'shipped' => ['label' => 'Đã giao', 'color' => 'dark'],
                                    ];
                                    $s = $statusMap[$order->status] ?? [
                                        'label' => $order->status ?? '—',
                                        'color' => 'secondary',
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $order->job_no }}</td>
                                    <td>{{ $order->fty_po ?: '—' }}</td>
                                    <td>{{ $order->ma_hh ?: '—' }}</td>
                                    <td>{{ $order->color ?: '—' }}</td>
                                    <td class="text-end">{{ number_format($order->qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($order->yrd, 2) }}</td>
                                    <td>{{ $order->chart ?: '—' }}</td>
                                    <td>{{ $order->lenh_sanxuat ?: '—' }}</td>
                                    <td>{{ $order->sig_need_date?->format('d/m/Y') ?: '—' }}</td>
                                    <td><span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-muted text-center">Không có đơn hàng</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══ TRACKING CHI TIẾT ═══ --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="fa-solid fa-truck-fast me-1"></i>Tracking chi tiết
                    <span class="badge bg-info ms-1">{{ $trackings->count() }}</span>
                </h6>
                @if ($trackings->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Order (Job No)</th>
                                    <th>Mã HH</th>
                                    <th>Màu</th>
                                    <th>Kích</th>
                                    <th>Công đoạn</th>
                                    <th class="text-end">SL Đơn hàng</th>
                                    <th class="text-end">SL Sản xuất</th>
                                    <th>Ngày tạo</th>
                                    <th>Cập nhật</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trackings as $item)
                                    @php
                                        $stageInfo = $stages[$item->cong_doan] ?? [
                                            'icon' => 'fa-question',
                                            'color' => 'secondary',
                                            'order' => -1,
                                        ];
                                        $currentOrder = $stageInfo['order'];
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $item->order->job_no ?? '—' }}</td>
                                        <td>{{ $item->size }}</td>
                                        <td>{{ $item->mau }}</td>
                                        <td>{{ $item->kich }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                @foreach ($stages as $stageName => $info)
                                                    @php
                                                        $isDone = $info['order'] < $currentOrder;
                                                        $isCurrent = $stageName === $item->cong_doan;
                                                    @endphp
                                                    <span
                                                        class="rounded-circle d-inline-flex align-items-center justify-content-center
                                                        {{ $isCurrent ? 'bg-' . $info['color'] . ' text-white' : ($isDone ? 'bg-' . $info['color'] . ' text-white opacity-50' : 'bg-light text-muted border') }}"
                                                        style="width:22px;height:22px;font-size:.55rem"
                                                        title="{{ $stageName }}">
                                                        <i class="fa-solid {{ $info['icon'] }}"></i>
                                                    </span>
                                                    @if (!$loop->last)
                                                        <i class="fa-solid fa-chevron-right"
                                                            style="font-size:.4rem;color:#ccc"></i>
                                                    @endif
                                                @endforeach
                                                <span class="badge bg-{{ $stageInfo['color'] }} ms-1"
                                                    style="font-size:.65rem">{{ $item->cong_doan }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">{{ number_format($item->sl_don_hang, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->sl_san_xuat, 2) }}</td>
                                        <td style="font-size:.8rem">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td style="font-size:.8rem">{{ $item->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.order-tracking.edit', $item) }}"
                                                class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">Chưa có tracking nào cho lô này.</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Check All batch
        document.getElementById('checkAllBatch')?.addEventListener('change', function() {
            document.querySelectorAll('.batch-check').forEach(cb => cb.checked = this.checked);
        });

        // Áp dụng % hao hụt
        function applyHaoHut() {
            const pct = parseFloat(document.getElementById('pctHaoHut')?.value || 10);
            document.querySelectorAll('#tblBatchSx .sl-dat-input').forEach(input => {
                const base = parseFloat(input.dataset.base) || 0;
                const slPlusHh = base * (1 + pct / 100);
                const row = input.closest('tr');
                row.querySelector('.sl-plus-hh').textContent = slPlusHh.toLocaleString('en', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });
        }
        document.getElementById('btnApplyHaoHut')?.addEventListener('click', applyHaoHut);

        // Khi thay đổi SL sản xuất → cập nhật SL + %HH
        document.querySelectorAll('#tblBatchSx .sl-dat-input').forEach(input => {
            input.addEventListener('input', function() {
                const pct = parseFloat(document.getElementById('pctHaoHut')?.value || 10);
                const val = parseFloat(this.value) || 0;
                const slPlusHh = val * (1 + pct / 100);
                const row = this.closest('tr');
                row.querySelector('.sl-plus-hh').textContent = slPlusHh.toLocaleString('en', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                this.dataset.base = val;
            });
        });
    </script>
@endsection
