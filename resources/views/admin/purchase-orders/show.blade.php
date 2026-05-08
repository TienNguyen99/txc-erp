@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">
            <i class="fa-solid fa-cart-shopping me-2"></i>PO: {{ $purchaseOrder->so_po }}
        </h4>
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Danh sách
        </a>
    </div>

    <div class="row g-3">
        {{-- Thông tin PO --}}
        <div class="col-lg-4">
            <div class="card-page h-100">
                @php $info = \App\Models\PurchaseOrder::$trangThaiLabels[$purchaseOrder->trang_thai] ?? ['label'=>'?','color'=>'secondary']; @endphp
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Thông tin</h6>
                <table class="table table-sm table-borderless" style="font-size:.875rem">
                    <tr><td class="text-muted" style="width:130px">Số PO</td><td class="fw-semibold">{{ $purchaseOrder->so_po }}</td></tr>
                    <tr><td class="text-muted">NCC</td><td>{{ $purchaseOrder->nhaCungCap?->ten_ncc ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Ngày đặt</td><td>{{ $purchaseOrder->ngay_dat?->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Giao DK</td><td>{{ $purchaseOrder->ngay_giao_du_kien?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Ngày nhận</td><td>{{ $purchaseOrder->ngay_nhan_thuc_te?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Trạng thái</td><td><span class="badge bg-{{ $info['color'] }}">{{ $info['label'] }}</span></td></tr>
                    <tr><td class="text-muted">Tạo bởi</td><td>{{ $purchaseOrder->createdBy?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Tổng tiền</td><td class="fw-bold text-primary">{{ number_format($purchaseOrder->tong_tien, 0, ',', '.') }} ₫</td></tr>
                </table>
                @if($purchaseOrder->ghi_chu)
                <div class="mt-2 p-2" style="background:#f8fafc;border-radius:8px;font-size:.8rem">
                    <i class="fa-solid fa-note-sticky me-1 text-muted"></i>{{ $purchaseOrder->ghi_chu }}
                </div>
                @endif

                {{-- Đổi trạng thái --}}
                @if(!in_array($purchaseOrder->trang_thai, ['received', 'cancelled']))
                <hr>
                <h6 class="fw-bold mb-2">Cập nhật trạng thái</h6>
                <form method="POST" action="{{ route('admin.purchase-orders.update-status', $purchaseOrder) }}">
                    @csrf
                    <div class="d-flex gap-2">
                        <select name="trang_thai" class="form-select form-select-sm">
                            @foreach(\App\Models\PurchaseOrder::$trangThaiLabels as $val => $i)
                            <option value="{{ $val }}" {{ $purchaseOrder->trang_thai == $val ? 'selected' : '' }}>
                                {{ $i['label'] }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm text-nowrap">Lưu</button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="fa-solid fa-circle-info me-1"></i>Chọn "Đã nhận hàng" sẽ tự động nhập kho NVL.
                    </small>
                </form>
                @endif
            </div>
        </div>

        {{-- Danh sách NVL --}}
        <div class="col-lg-8">
            <div class="card-page">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-list me-2 text-primary"></i>Danh sách NVL đặt mua</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Mã NVL</th>
                                <th>Tên</th>
                                <th class="text-center">ĐVT</th>
                                <th class="text-end">SL đặt</th>
                                <th class="text-end">SL đã nhận</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->ma_hh }}</td>
                                <td>{{ $item->ten_hh }}</td>
                                <td class="text-center">{{ $item->don_vi }}</td>
                                <td class="text-end">{{ number_format($item->so_luong, 2) }}</td>
                                <td class="text-end">
                                    <span class="{{ $item->da_nhan >= $item->so_luong ? 'text-success fw-bold' : '' }}">
                                        {{ number_format($item->da_nhan, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">{{ number_format($item->don_gia, 0, ',', '.') }}</td>
                                <td class="text-end text-primary fw-semibold">
                                    {{ number_format($item->thanh_tien, 0, ',', '.') }} ₫
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($purchaseOrder->tong_tien, 0, ',', '.') }} ₫
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Attachments --}}
                <hr>
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-paperclip me-2 text-primary"></i>Tài liệu đính kèm</h6>
                @include('admin.partials.attachments', [
                    'attachable_type' => 'App\\Models\\PurchaseOrder',
                    'attachable_id'   => $purchaseOrder->id,
                    'attachments'     => $purchaseOrder->attachments ?? collect(),
                ])
            </div>
        </div>
    </div>
</div>
@endsection
