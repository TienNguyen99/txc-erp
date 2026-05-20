@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.lenh-san-xuat.index') }}" class="text-decoration-none"
                    style="font-size:.85rem;color:var(--primary);font-weight:500">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
                </a>
                <h4 class="page-title mt-2 mb-0">
                    <i class="fa-solid fa-clipboard-list me-2"></i>Lệnh SX — <span
                        class="text-primary">{{ $lenh->lenh_so }}</span>
                </h4>
                <div class="mt-1" style="font-size:.85rem">
                    <span class="text-muted">Chart:</span>
                    <span class="badge bg-secondary">{{ $lenh->chart }}</span>
                    <span class="text-muted ms-2">Nhóm:</span>
                    <span class="badge bg-info">{{ $lenh->nhom_hh }}</span>
                    <span class="text-muted ms-2">% Hao hụt:</span>
                    <strong>{{ $lenh->pct_hao_hut }}%</strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Switch lệnh --}}
                <select class="form-select form-select-sm" style="width:220px"
                    onchange="if(this.value) window.location.href=this.value">
                    <option value="">-- Chuyển lệnh --</option>
                    @foreach ($allLenh as $id => $so)
                        <option value="{{ route('admin.lenh-san-xuat.show', $id) }}"
                            {{ $id == $lenh->id ? 'selected' : '' }}>
                            {{ $so }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('admin.warehouse-transactions.lenh-xuat-vat-tu', $lenh->id) }}" class="btn btn-warning btn-sm text-dark">
                    <i class="fa-solid fa-boxes-stacked me-1"></i>Yêu cầu Xuất vật tư
                </a>
                <a href="{{ route('admin.lenh-san-xuat.export', $lenh) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i>In lệnh SX (Excel)
                </a>
            </div>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            {{-- ═══ THỐNG KÊ ═══ --}}
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Tổng Mã HH</div>
                            <h3 class="fw-bold text-primary mb-0">{{ $stats->total_items }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Đã lên lệnh</div>
                            <h3 class="fw-bold text-success mb-0">{{ $stats->da_len_lenh }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Tổng YRD</div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats->tong_yrd, 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Cần SX (+HH)</div>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($stats->tong_can_sx, 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Đã SX</div>
                            <h3 class="fw-bold text-warning mb-0">{{ number_format($stats->tong_da_sx, 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-muted" style="font-size:.75rem">Tồn kho</div>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($stats->tong_ton_kho, 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ BẢNG TIẾN ĐỘ LỆNH CON ═══ --}}
            <form method="POST" action="{{ route('admin.lenh-san-xuat.toggle-items', $lenh) }}">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fa-solid fa-chart-bar me-1"></i>Chi tiết lệnh SX theo Mã HH
                    </h6>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi (Lên lệnh & SL)
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center no-sort" style="width:40px"><input type="checkbox" id="checkAll"></th>
                                <th class="text-center">STT</th>
                                <th>Lệnh con</th>
                                <th>Mã HH</th>
                                <th>Tên sản phẩm</th>
                                <th>Màu</th>
                                <th class="text-end">Tổng YRD</th>
                                <th class="text-end">Cần SX</th>
                                <th class="text-end">Đã SX</th>
                                <th class="text-end">Tồn kho</th>
                                <th class="no-sort" style="min-width:180px">Tiến độ</th>
                                <th class="text-center">Đã lên lệnh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $pctKho = $item->tong_yrd > 0 ? min(100, round(($item->ton_kho / $item->tong_yrd) * 100)) : 0;
                                    $pctSx = $item->tong_yrd > 0 ? min(100 - $pctKho, round(($item->sl_da_sx / $item->tong_yrd) * 100)) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="items[{{ $loop->index }}][selected]" value="1"
                                            class="item-check" @if ($item->da_len_lenh) checked @endif>
                                        <input type="hidden" name="items[{{ $loop->index }}][id]"
                                            value="{{ $item->id }}">
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->stt }}</td>
                                    <td><span class="badge bg-primary" style="font-size:.8rem">{{ $item->lenh_child }}</span></td>
                                    <td class="fw-semibold">{{ $item->ma_hh }}</td>
                                    <td>{{ $item->ten_hh ?: '—' }}</td>
                                    <td>{{ $item->mau ?: '—' }}</td>
                                    <td class="text-end">{{ number_format($item->tong_yrd, 2) }}</td>
                                    <td class="text-end">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end text-info fw-semibold mx-auto" 
                                            name="items[{{ $loop->index }}][sl_can_sx]" value="{{ round($item->sl_can_sx, 2) }}" style="width:90px">
                                    </td>
                                    <td class="text-end {{ $item->sl_da_sx > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                                        {{ number_format($item->sl_da_sx, 2) }}
                                    </td>
                                    <td class="text-end {{ $item->ton_kho > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                        {{ number_format($item->ton_kho, 2) }}
                                    </td>
                                    <td>
                                        <div class="progress" style="height:20px">
                                            @if ($pctKho > 0)
                                                <div class="progress-bar bg-success" style="width:{{ $pctKho }}%"
                                                    title="Tồn kho">
                                                    @if ($pctKho >= 15) {{ $pctKho }}% @endif
                                                </div>
                                            @endif
                                            @if ($pctSx > 0)
                                                <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated"
                                                    style="width:{{ $pctSx }}%" title="Đang SX">
                                                    @if ($pctSx >= 15) {{ $pctSx }}% @endif
                                                </div>
                                            @endif
                                            @if ($pctKho == 0 && $pctSx == 0)
                                                <div class="progress-bar bg-light text-dark" style="width:100%">Chưa SX</div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between" style="font-size:.65rem;margin-top:2px">
                                            <span class="text-success"><i class="fa-solid fa-warehouse me-1"></i>Kho</span>
                                            <span class="text-warning"><i class="fa-solid fa-industry me-1"></i>SX</span>
                                            <span>{{ $item->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($item->da_len_lenh)
                                            <span class="badge bg-primary"><i class="fa-solid fa-check"></i> Đã lên lệnh</span>
                                        @else
                                            <span class="badge bg-secondary">Chưa lên lệnh</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark fw-bold">
                            <tr>
                                @php $activeItems = $items->where('da_len_lenh', true); @endphp
                                <td colspan="6">Tổng ({{ $activeItems->count() }} mã đã lên lệnh)</td>
                                <td class="text-end">{{ number_format($activeItems->sum('tong_yrd'), 2) }}</td>
                                <td class="text-end">{{ number_format($activeItems->sum('sl_can_sx'), 2) }}</td>
                                <td class="text-end">{{ number_format($activeItems->sum('sl_da_sx'), 2) }}</td>
                                <td class="text-end">{{ number_format($activeItems->sum('ton_kho'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>

            {{-- ═══ LINK QR SCAN ═══ --}}
            <div class="card border-info mt-4 mb-4">
                <div class="card-body py-2">
                    <i class="fa-solid fa-qrcode text-info me-1"></i>
                    <strong>QR Scan:</strong>
                    <a href="{{ route('lenh-sx.index', $lenh->lenh_so) }}" target="_blank" class="text-decoration-none">
                        {{ route('lenh-sx.index', $lenh->lenh_so) }}
                    </a>
                    <small class="text-muted ms-2">— Công nhân quét QR mã này để báo cáo SX</small>
                </div>
            </div>

            {{-- ═══ DANH SÁCH ĐƠN HÀNG TRONG CHART ═══ --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="fa-solid fa-file-invoice me-1"></i>Đơn hàng trong Chart
                    <span class="badge bg-primary ms-1">{{ $orders->count() }}</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Job No</th>
                                <th>Fty PO</th>
                                <th>PL Number</th>
                                <th>Mã HH</th>
                                <th>Màu</th>
                                <th class="text-end">QTY</th>
                                <th class="text-end">YRD</th>
                                <th>Ngày giao</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'Chờ xử lý', 'color' => 'warning'],
                                        'in_production' => ['label' => 'Đang SX', 'color' => 'info'],
                                        'done' => ['label' => 'Hoàn thành', 'color' => 'success'],
                                        'shipped' => ['label' => 'Đã giao', 'color' => 'dark'],
                                    ];
                                    $s = $statusMap[$order->status] ?? ['label' => $order->status ?? '—', 'color' => 'secondary'];
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $order->job_no }}</td>
                                    <td>{{ $order->fty_po ?: '—' }}</td>
                                    <td>{{ $order->pl_number ?: '—' }}</td>
                                    <td>{{ $order->ma_hh ?: '—' }}</td>
                                    <td>{{ $order->color ?: '—' }}</td>
                                    <td class="text-end">{{ number_format($order->qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($order->yrd, 2) }}</td>
                                    <td>{{ $order->sig_need_date?->format('d/m/Y') ?: '—' }}</td>
                                    <td><span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- ═══ LỊCH SỬ XUẤT VẬT TƯ ═══ --}}
            @php
                $xuatVatTuHistory = \App\Models\WarehouseTransaction::where('lenh_sx', $lenh->lenh_so)
                    ->where('cong_doan', 'XUATKHO')
                    ->latest('ngay')
                    ->get();
            @endphp
            @if($xuatVatTuHistory->count() > 0)
            <div class="mb-4 mt-5">
                <h6 class="fw-bold mb-2 text-warning">
                    <i class="fa-solid fa-boxes-stacked me-1"></i>Lịch sử Xuất vật tư cho Lệnh này
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-warning text-dark">
                            <tr>
                                <th>Ngày xuất</th>
                                <th>Mã NVL</th>
                                <th>Người xuất</th>
                                <th class="text-end">Số lượng</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($xuatVatTuHistory as $xuat)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($xuat->ngay)->format('d/m/Y') }}</td>
                                    <td class="fw-bold">{{ $xuat->ma_hh }}</td>
                                    <td>{{ $xuat->ma_nv }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($xuat->so_luong, 2) }}</td>
                                    <td>{{ $xuat->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ═══ CHI PHÍ NVL ƯỚC TÍNH (Module 9) ═══ --}}
            @php
                $activeItems2 = $items->where('da_len_lenh', true);
                $chiPhiRows = [];
                $tongChiPhi = 0;
                foreach ($activeItems2 as $item2) {
                    $hh = \App\Models\DanhMucHangHoa::where('ma_hh', $item2->ma_hh)->first();
                    if (!$hh) continue;
                    $boms = \App\Models\DinhMucNvl::where('san_pham_id', $hh->id)->with('nguyenLieu')->get();
                    foreach ($boms as $bom) {
                        $nvl = $bom->nguyenLieu;
                        if (!$nvl) continue;
                        $sl = $item2->sl_can_sx * $bom->dinh_muc * (1 + ($bom->hao_hut ?? 0) / 100);
                        $cp = $sl * ($nvl->gia_nvl ?? 0);
                        $tongChiPhi += $cp;
                        $chiPhiRows[] = [
                            'ma_hh'    => $item2->ma_hh,
                            'ma_nvl'   => $nvl->ma_hh,
                            'ten_nvl'  => $nvl->ten_hh,
                            'sl'       => $sl,
                            'don_gia'  => $nvl->gia_nvl ?? 0,
                            'chi_phi'  => $cp,
                        ];
                    }
                }
            @endphp
            @if(count($chiPhiRows) > 0)
            <div class="mb-4 mt-4">
                <h6 class="fw-bold mb-2 text-success">
                    <i class="fa-solid fa-calculator me-1"></i>Chi phí NVL ước tính
                    <small class="text-muted fw-normal ms-1" style="font-size:.8rem">(dựa trên BOM + Giá NVL)</small>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Mã TP</th>
                                <th>Mã NVL</th>
                                <th>Tên NVL</th>
                                <th class="text-end">SL cần (có HH)</th>
                                <th class="text-end">Đơn giá (₫)</th>
                                <th class="text-end">Chi phí (₫)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chiPhiRows as $row)
                            <tr>
                                <td class="fw-semibold" style="font-size:.8rem">{{ $row['ma_hh'] }}</td>
                                <td class="fw-semibold text-primary">{{ $row['ma_nvl'] }}</td>
                                <td>{{ $row['ten_nvl'] }}</td>
                                <td class="text-end">{{ number_format($row['sl'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['don_gia'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($row['chi_phi'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Tổng chi phí NVL ước tính:</td>
                                <td class="text-end fw-bold text-success" style="font-size:1.05rem">
                                    {{ number_format($tongChiPhi, 0, ',', '.') }} ₫
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i>
                    Để tính chính xác, hãy cập nhật <strong>Giá NVL</strong> trong Danh mục Hàng hóa.
                </small>
            </div>
            @endif

            {{-- ═══ FILE ĐÍNH KÈM ═══ --}}
            <div class="mt-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-paperclip me-2 text-primary"></i>Tài liệu đính kèm</h6>
                @include('admin.partials.attachments', [
                    'attachable_type' => 'App\\Models\\LenhSanXuat',
                    'attachable_id'   => $lenh->id,
                    'attachments'     => $lenh->attachments ?? collect(),
                ])
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script>
        document.getElementById('checkAll')?.addEventListener('change', function() {
            document.querySelectorAll('.item-check').forEach(cb => cb.checked = this.checked);
        });
    </script>
@endsection
