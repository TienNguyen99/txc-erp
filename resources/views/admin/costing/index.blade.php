@extends('layouts.app')

@section('page-title', 'Giá vốn')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-calculator me-2"></i>Giá vốn</h4>
            <form method="GET" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label mb-0">Tháng</label>
                    <select name="month" class="form-select form-select-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>{{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label mb-0">Năm</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
                </div>
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
            </form>
        </div>

        <div class="card-page mb-3">
            @include('admin.partials.alert')

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted" style="font-size:.75rem">Giá thành SX</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats->production_total, 0) }}</div>
                        <small class="text-muted">VND trong kỳ</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted" style="font-size:.75rem">Giá vốn đã xuất</div>
                        <div class="fs-4 fw-bold text-danger">{{ number_format($stats->shipment_cogs, 0) }}</div>
                        <small class="text-muted">COGS giao hàng</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted" style="font-size:.75rem">Doanh thu xuất kho</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($stats->shipment_revenue, 0) }}</div>
                        <small class="text-muted">Theo giá USD x tỷ giá</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted" style="font-size:.75rem">Lãi gộp</div>
                        <div class="fs-4 fw-bold {{ $stats->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($stats->gross_profit, 0) }}
                        </div>
                        <small class="text-muted">{{ number_format($stats->gross_margin_pct, 2) }}%</small>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.costing.overheads.store') }}" class="border rounded-3 p-3 mb-4">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="allocation_basis" value="output_qty">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-coins me-1"></i>Chi phí phân bổ tháng {{ $month }}/{{ $year }}</h6>
                    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu chi phí</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Nhân công trực tiếp</label>
                        <input type="number" step="0.01" min="0" name="labor_cost_vnd" class="form-control"
                            value="{{ old('labor_cost_vnd', $overhead->labor_cost_vnd) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sản xuất chung</label>
                        <input type="number" step="0.01" min="0" name="factory_overhead_vnd" class="form-control"
                            value="{{ old('factory_overhead_vnd', $overhead->factory_overhead_vnd) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Chi phí khác</label>
                        <input type="number" step="0.01" min="0" name="other_cost_vnd" class="form-control"
                            value="{{ old('other_cost_vnd', $overhead->other_cost_vnd) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="note" class="form-control" value="{{ old('note', $overhead->note) }}">
                    </div>
                </div>
            </form>

            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-box-open me-1"></i>Giá vốn từng mã hàng</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã HH</th>
                                <th>Tên hàng</th>
                                <th class="text-end">SL SX</th>
                                <th class="text-end">SL xuất</th>
                                <th class="text-end">Tồn ước tính</th>
                                <th class="text-end">NVL</th>
                                <th class="text-end">NC/SXC</th>
                                <th class="text-end">Tổng giá thành</th>
                                <th class="text-end">Giá vốn / đơn vị</th>
                                <th class="text-end">Giá vốn đã xuất</th>
                                <th class="text-end">Doanh thu</th>
                                <th class="text-end">Lãi gộp</th>
                                <th>Nguồn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemCosts as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row->ma_hh }}</td>
                                    <td>{{ $row->ten_hh ?: '—' }}</td>
                                    <td class="text-end">{{ number_format($row->produced_qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->shipped_qty, 2) }}</td>
                                    <td class="text-end {{ $row->ending_qty_estimate < 0 ? 'text-danger' : '' }}">
                                        {{ number_format($row->ending_qty_estimate, 2) }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($row->material_actual_vnd > 0 ? $row->material_actual_vnd : $row->material_standard_vnd, 0) }}
                                    </td>
                                    <td class="text-end">{{ number_format($row->conversion_vnd, 0) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($row->production_cost_vnd, 0) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row->unit_cost_vnd, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->cogs_vnd, 0) }}</td>
                                    <td class="text-end">{{ number_format($row->revenue_vnd, 0) }}</td>
                                    <td class="text-end {{ $row->gross_profit_vnd >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($row->gross_profit_vnd, 0) }}
                                        @if ($row->revenue_vnd > 0)
                                            <div class="text-muted" style="font-size:.7rem">{{ number_format($row->gross_margin_pct, 2) }}%</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row->cost_source === 'production')
                                            <span class="badge bg-success">SX</span>
                                        @elseif ($row->cost_source === 'missing')
                                            <span class="badge bg-danger">Thiếu giá</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $row->cost_source }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-center text-muted">Chưa có dữ liệu để tính giá vốn mã hàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-industry me-1"></i>Giá thành theo lệnh sản xuất</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Lệnh SX</th>
                                <th>Mã HH</th>
                                <th class="text-end">SL đạt</th>
                                <th class="text-end">NVL thực tế</th>
                                <th class="text-end">NVL định mức</th>
                                <th class="text-end">NC/SXC phân bổ</th>
                                <th class="text-end">Tổng giá thành</th>
                                <th class="text-end">Đơn giá vốn</th>
                                <th>Nguồn NVL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productionCosts as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row->lenh_sx }}</td>
                                    <td>{{ $row->ma_hh }}</td>
                                    <td class="text-end">{{ number_format($row->output_qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->material_actual_vnd, 0) }}</td>
                                    <td class="text-end">{{ number_format($row->material_standard_vnd, 0) }}</td>
                                    <td class="text-end">{{ number_format($row->allocated_conversion_vnd, 0) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($row->total_cost_vnd, 0) }}</td>
                                    <td class="text-end">{{ number_format($row->unit_cost_vnd, 2) }}</td>
                                    <td>
                                        @if ($row->cost_source === 'actual_issue')
                                            <span class="badge bg-success">Xuất vật tư</span>
                                        @else
                                            <span class="badge bg-warning text-dark">BOM định mức</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Chưa có dữ liệu sản xuất trong kỳ.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-truck-ramp-box me-1"></i>Giá vốn hàng đã xuất</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Lệnh SX</th>
                                <th>Mã HH</th>
                                <th class="text-end">SL xuất</th>
                                <th class="text-end">Đơn giá vốn</th>
                                <th class="text-end">Giá vốn</th>
                                <th class="text-end">Doanh thu</th>
                                <th class="text-end">Lãi gộp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipmentCosts as $row)
                                <tr>
                                    <td>{{ $row->ngay?->format('d/m/Y') }}</td>
                                    <td>{{ $row->lenh_sx ?: '—' }}</td>
                                    <td>{{ $row->ma_hh }}</td>
                                    <td class="text-end">{{ number_format($row->qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->unit_cost_vnd, 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row->cogs_vnd, 0) }}</td>
                                    <td class="text-end">{{ number_format($row->revenue_vnd, 0) }}</td>
                                    <td class="text-end {{ $row->gross_profit_vnd >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($row->gross_profit_vnd, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Chưa có giao dịch xuất hàng trong kỳ.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-scale-balanced me-1"></i>Đơn giá NVL bình quân</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã NVL</th>
                                <th class="text-end">SL nhập tham chiếu</th>
                                <th class="text-end">Giá trị tham chiếu</th>
                                <th class="text-end">Đơn giá vốn</th>
                                <th>Nguồn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unitCosts as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['ma_hh'] }}</td>
                                    <td class="text-end">{{ number_format($row['qty'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['value_vnd'], 0) }}</td>
                                    <td class="text-end">{{ number_format($row['unit_cost_vnd'], 2) }}</td>
                                    <td>{{ $row['source'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Chưa có đơn giá NVL.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
