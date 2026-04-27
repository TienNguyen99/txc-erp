@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-industry me-2"></i>Lệnh Sản Xuất</h4>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')

            {{-- ═══ FILTER CHART ═══ --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-0" style="font-size:.8rem">Chart <small class="text-muted">(chọn
                            nhiều)</small></label>
                    <select id="chartSelect" name="chart[]" multiple placeholder="Tìm & chọn Chart...">
                        @foreach ($charts as $c)
                            @php
                                $lenhs = \App\Models\LenhSanXuat::where('chart', $c)->get();
                                $label = $c;
                                if ($lenhs->count() > 0) {
                                    $hasDaLenLenh = \App\Models\LenhSanXuatItem::whereIn('lenh_san_xuat_id', $lenhs->pluck('id'))
                                        ->where('da_len_lenh', true)->exists();
                                    if ($hasDaLenLenh) {
                                        $label .= ' (Đã lên lệnh)';
                                    } else {
                                        $label .= ' (Có ' . $lenhs->count() . ' lệnh)';
                                    }
                                }
                            @endphp
                            <option value="{{ $c }}"
                                {{ in_array($c, $chartFilter) ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Lọc</button>
                    <a href="{{ route('admin.lenh-san-xuat.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset
                    </a>
                </div>
            </form>

            {{-- ═══ TẠO LỆNH MỚI TỪ CHART ═══ --}}
            @if (!empty($chartFilter))
                @foreach ($chartFilter as $chart)
                    @php
                        $lenhs = \App\Models\LenhSanXuat::where('chart', $chart)->get();
                        $hasLenh = $lenhs->count() > 0;
                        $isDaLenLenh = false;
                        if ($hasLenh) {
                            $isDaLenLenh = \App\Models\LenhSanXuatItem::whereIn('lenh_san_xuat_id', $lenhs->pluck('id'))
                                ->where('da_len_lenh', true)->exists();
                        }
                    @endphp
                    <div class="card {{ $isDaLenLenh ? 'border-success' : 'border-primary' }} mb-3">
                        <div class="card-body py-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fa-solid fa-chart-pie {{ $isDaLenLenh ? 'text-success' : 'text-primary' }} me-1"></i>
                                <strong>Chart: {{ $chart }}</strong>
                                @if ($hasLenh)
                                    <span class="badge bg-secondary ms-2">
                                        <i class="fa-solid fa-list me-1"></i>Đang có {{ $lenhs->count() }} lệnh
                                    </span>
                                @endif
                                @if ($isDaLenLenh)
                                    <span class="badge bg-success ms-2">
                                        <i class="fa-solid fa-check me-1"></i>Đã lên lệnh rồi
                                    </span>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-2 align-items-center">
                                @if ($hasLenh)
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-eye me-1"></i>Xem lệnh
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach($lenhs as $l)
                                                <li><a class="dropdown-item" href="{{ route('admin.lenh-san-xuat.show', $l) }}">{{ $l->lenh_so }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('admin.lenh-san-xuat.store') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="chart" value="{{ $chart }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="mb-0" style="font-size:.8rem">% Hao hụt:</label>
                                        <input type="number" name="pct_hao_hut" value="10" min="0" max="100"
                                            step="0.5" class="form-control form-control-sm" style="width:80px">
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Tạo thêm lệnh SX cho Chart {{ $chart }}?')">
                                            <i class="fa-solid fa-plus me-1"></i>Tạo lệnh SX
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- ═══ BẢNG TỔNG HỢP MÃ HH (khi có filter) ═══ --}}
            @if ($summary->count())
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-chart-bar me-1"></i>Tổng hợp theo Mã HH
                        — Chart: <span class="text-dark">{{ implode(', ', $chartFilter) }}</span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã HH</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Nhóm</th>
                                    <th>Màu</th>
                                    <th class="text-center">Số PO</th>
                                    <th class="text-end">Tổng YRD</th>
                                    <th class="text-end">Đang SX</th>
                                    <th class="text-end">Tồn kho</th>
                                    <th class="text-end">Thiếu / Dư</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary as $row)
                                    @php $diff = $row->ton_kho - $row->tong_qty; @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $row->ma_hh }}</td>
                                        <td>{{ $row->ten_hh ?: '—' }}</td>
                                        <td><span class="badge bg-secondary">{{ $row->nhom_hh ?: '—' }}</span></td>
                                        <td>{{ $row->mau ?: '—' }}</td>
                                        <td class="text-center">{{ $row->so_don }}</td>
                                        <td class="text-end">{{ number_format($row->tong_qty, 2) }}</td>
                                        <td class="text-end {{ $row->sl_production > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->sl_production, 2) }}
                                        </td>
                                        <td class="text-end {{ $row->ton_kho > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                            {{ number_format($row->ton_kho, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $diff >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                        </td>
                                        <td>
                                            @if ($row->da_len_lenh)
                                                <span class="badge bg-primary"><i class="fa-solid fa-check me-1"></i>Đã lên lệnh</span>
                                            @elseif ($row->du_hang)
                                                <span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i>Đủ hàng</span>
                                            @elseif ($row->sl_production > 0)
                                                <span class="badge bg-info"><i class="fa-solid fa-industry me-1"></i>Đang SX</span>
                                            @else
                                                <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Thiếu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark fw-bold">
                                <tr>
                                    <td colspan="4">Tổng ({{ $summary->count() }} mã)</td>
                                    <td class="text-center">{{ $summary->sum('so_don') }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('tong_qty'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('sl_production'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('ton_kho'), 2) }}</td>
                                    <td class="text-end">{{ number_format($summary->sum('ton_kho') - $summary->sum('tong_qty'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif (empty($chartFilter))
                <div class="alert alert-secondary mb-4">
                    <i class="fa-solid fa-filter me-1"></i>Chọn Chart ở trên để xem tổng hợp và tạo lệnh SX.
                </div>
            @endif

            {{-- ═══ DANH SÁCH LỆNH SX ĐÃ TẠO ═══ --}}
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-list me-1"></i>Danh sách lệnh SX</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Mã lệnh</th>
                            <th>Chart</th>
                            <th>Nhóm HH</th>
                            <th class="text-center">Số mã HH</th>
                            <th class="text-center">Đã lên lệnh</th>
                            <th>% Hao hụt</th>
                            <th>Ngày tạo</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lenhList as $lenh)
                            <tr>
                                <td>{{ $loop->iteration + ($lenhList->currentPage() - 1) * $lenhList->perPage() }}</td>
                                <td>
                                    <a href="{{ route('admin.lenh-san-xuat.show', $lenh) }}"
                                        class="fw-bold text-decoration-none text-primary">
                                        <i class="fa-solid fa-clipboard-list me-1"></i>{{ $lenh->lenh_so }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $lenh->chart }}</span></td>
                                <td>{{ $lenh->nhom_hh }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $lenh->items->count() }}</span>
                                </td>
                                <td class="text-center">
                                    @php $done = $lenh->items->where('da_len_lenh', true)->count(); @endphp
                                    <span class="badge {{ $done === $lenh->items->count() ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $done }}/{{ $lenh->items->count() }}
                                    </span>
                                </td>
                                <td>{{ $lenh->pct_hao_hut }}%</td>
                                <td>{{ $lenh->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.lenh-san-xuat.show', $lenh) }}"
                                        class="btn btn-outline-primary btn-xs">
                                        <i class="fa-solid fa-eye me-1"></i>Xem
                                    </a>
                                    <form method="POST" action="{{ route('admin.lenh-san-xuat.destroy', $lenh) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Xóa lệnh {{ $lenh->lenh_so }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">Chưa có lệnh SX nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $lenhList->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        new TomSelect('#chartSelect', {
            plugins: ['remove_button'],
            maxOptions: null,
            allowEmptyOption: false,
        });
    </script>
@endsection
