@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.order-tracking.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-truck-fast me-2"></i>Chi tiết Tracking #{{ $orderTracking->id }}
            </h4>
        </div>

        <div class="row g-4">
            {{-- ═══ THÔNG TIN TRACKING ═══ --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <i class="fa-solid fa-clipboard-list me-1"></i>Thông tin Tracking
                    </div>
                    <div class="card-body">
                        {{-- Công đoạn hiện tại --}}
                        @php
                            $stageInfo = $stages[$orderTracking->cong_doan] ?? [
                                'icon' => 'fa-question',
                                'color' => 'secondary',
                                'order' => -1,
                            ];
                            $currentOrder = $stageInfo['order'];
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:#f8f9fa">
                            @foreach ($stages as $stageName => $info)
                                @php
                                    $isDone = $info['order'] < $currentOrder;
                                    $isCurrent = $stageName === $orderTracking->cong_doan;
                                @endphp
                                <div class="text-center">
                                    <span
                                        class="rounded-circle d-inline-flex align-items-center justify-content-center
                                        {{ $isCurrent ? 'bg-' . $info['color'] . ' text-white' : ($isDone ? 'bg-' . $info['color'] . ' text-white opacity-50' : 'bg-light text-muted border') }}"
                                        style="width:32px;height:32px;font-size:.75rem">
                                        <i class="fa-solid {{ $info['icon'] }}"></i>
                                    </span>
                                    <div style="font-size:.6rem"
                                        class="{{ $isCurrent ? 'fw-bold text-' . $info['color'] : 'text-muted' }}">
                                        {{ $stageName }}
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <i class="fa-solid fa-chevron-right" style="font-size:.5rem;color:#ccc"></i>
                                @endif
                            @endforeach
                        </div>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px">PL Number</td>
                                <td class="fw-semibold">{{ $orderTracking->pl_number ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mã HH (Size)</td>
                                <td class="fw-semibold">{{ $orderTracking->size ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Màu</td>
                                <td>{{ $orderTracking->mau ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kích</td>
                                <td>{{ $orderTracking->kich ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Công đoạn</td>
                                <td><span class="badge bg-{{ $stageInfo['color'] }}"><i
                                            class="fa-solid {{ $stageInfo['icon'] }} me-1"></i>{{ $orderTracking->cong_doan ?: '—' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">SL Đơn hàng</td>
                                <td class="fw-bold">{{ number_format($orderTracking->sl_don_hang, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">SL Sản xuất</td>
                                <td class="fw-bold">{{ number_format($orderTracking->sl_san_xuat, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Ngày tạo</td>
                                <td>{{ $orderTracking->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cập nhật lần cuối</td>
                                <td>{{ $orderTracking->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>

                        <div class="mt-3">
                            <a href="{{ route('admin.order-tracking.edit', $orderTracking) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen me-1"></i>Sửa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ THÔNG TIN ORDER ═══ --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white py-2">
                        <i class="fa-solid fa-file-invoice me-1"></i>Thông tin Đơn hàng
                    </div>
                    <div class="card-body">
                        @if ($orderTracking->order)
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:140px">Job No</td>
                                    <td class="fw-semibold">{{ $orderTracking->order->job_no }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Fty PO</td>
                                    <td>{{ $orderTracking->order->fty_po ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Khách hàng</td>
                                    <td>{{ $orderTracking->order->khachHang->ten ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Mã HH</td>
                                    <td>{{ $orderTracking->order->ma_hh ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Màu</td>
                                    <td>{{ $orderTracking->order->color ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">QTY / YRD</td>
                                    <td>{{ number_format($orderTracking->order->qty, 2) }} /
                                        {{ number_format($orderTracking->order->yrd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Chart</td>
                                    <td>{{ $orderTracking->order->chart ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lệnh SX</td>
                                    <td>{{ $orderTracking->order->lenh_sanxuat ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Trạng thái</td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                'pending' => ['label' => 'Chờ xử lý', 'color' => 'warning'],
                                                'in_production' => ['label' => 'Đang SX', 'color' => 'info'],
                                                'done' => ['label' => 'Hoàn thành', 'color' => 'success'],
                                                'shipped' => ['label' => 'Đã giao', 'color' => 'dark'],
                                            ];
                                            $s = $statusMap[$orderTracking->order->status] ?? [
                                                'label' => $orderTracking->order->status,
                                                'color' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ngày giao</td>
                                    <td>{{ $orderTracking->order->sig_need_date?->format('d/m/Y') ?: '—' }}</td>
                                </tr>
                            </table>
                        @else
                            <p class="text-muted mb-0">Không tìm thấy thông tin đơn hàng.</p>
                        @endif
                    </div>
                </div>

                {{-- ═══ TÌNH TRẠNG KHO & SẢN XUẤT ═══ --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success text-white py-2">
                        <i class="fa-solid fa-warehouse me-1"></i>Tình trạng Kho & Sản xuất —
                        {{ $orderTracking->size ?: 'N/A' }}
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="text-muted" style="font-size:.75rem">Đang sản xuất</div>
                                <h5 class="fw-bold text-info mb-0">{{ number_format($slProduction, 2) }}</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-muted" style="font-size:.75rem">Đã nhập kho</div>
                                <h5 class="fw-bold text-success mb-0">{{ number_format($nhap, 2) }}</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-muted" style="font-size:.75rem">Tồn kho</div>
                                <h5 class="fw-bold {{ $tonKho > 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($tonKho, 2) }}</h5>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem">Tổng nhập</div>
                                <span class="fw-semibold">{{ number_format($nhap, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.75rem">Tổng xuất</div>
                                <span class="fw-semibold">{{ number_format($xuat, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ TRACKING CÙNG LÔ (PL Number) ═══ --}}
        @if ($relatedByPl->count())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-info text-white py-2">
                    <i class="fa-solid fa-layer-group me-1"></i>Tracking cùng lô — PL: {{ $orderTracking->pl_number }}
                    <span class="badge bg-light text-info ms-2">{{ $relatedByPl->count() }} mục khác</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order (Job No)</th>
                                    <th>Mã HH</th>
                                    <th>Màu</th>
                                    <th>Công đoạn</th>
                                    <th class="text-end">SL Đơn hàng</th>
                                    <th class="text-end">SL Sản xuất</th>
                                    <th>Ngày tạo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($relatedByPl as $item)
                                    @php $si = $stages[$item->cong_doan] ?? ['icon' => 'fa-question', 'color' => 'secondary']; @endphp
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td class="fw-semibold">{{ $item->order->job_no ?? '—' }}</td>
                                        <td>{{ $item->size }}</td>
                                        <td>{{ $item->mau }}</td>
                                        <td><span class="badge bg-{{ $si['color'] }}"><i
                                                    class="fa-solid {{ $si['icon'] }} me-1"></i>{{ $item->cong_doan }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($item->sl_don_hang, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->sl_san_xuat, 2) }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.order-tracking.show', $item) }}"
                                                class="btn btn-outline-primary btn-xs">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══ TRACKING CÙNG ORDER ═══ --}}
        @if ($relatedByOrder->count())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-secondary text-white py-2">
                    <i class="fa-solid fa-link me-1"></i>Tracking cùng Order — {{ $orderTracking->order->job_no ?? '' }}
                    <span class="badge bg-light text-secondary ms-2">{{ $relatedByOrder->count() }} mục khác</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>PL Number</th>
                                    <th>Mã HH</th>
                                    <th>Màu</th>
                                    <th>Công đoạn</th>
                                    <th class="text-end">SL Đơn hàng</th>
                                    <th class="text-end">SL Sản xuất</th>
                                    <th>Ngày tạo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($relatedByOrder as $item)
                                    @php $si = $stages[$item->cong_doan] ?? ['icon' => 'fa-question', 'color' => 'secondary']; @endphp
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->pl_number }}</td>
                                        <td>{{ $item->size }}</td>
                                        <td>{{ $item->mau }}</td>
                                        <td><span class="badge bg-{{ $si['color'] }}"><i
                                                    class="fa-solid {{ $si['icon'] }} me-1"></i>{{ $item->cong_doan }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($item->sl_don_hang, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->sl_san_xuat, 2) }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.order-tracking.show', $item) }}"
                                                class="btn btn-outline-primary btn-xs">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
