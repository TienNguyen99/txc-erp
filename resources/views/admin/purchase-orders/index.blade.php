@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="fa-solid fa-cart-shopping me-2"></i>Đặt Hàng NVL (Purchase Order)</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.purchase-orders.create-from-bom') }}" class="btn btn-outline-warning btn-sm">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Tạo từ BOM
            </a>
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Tạo PO
            </a>
        </div>
    </div>
    <div class="card-page">
        @include('admin.partials.alert')
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Tìm số PO..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="trang_thai" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    @foreach(\App\Models\PurchaseOrder::$trangThaiLabels as $val => $info)
                    <option value="{{ $val }}" {{ request('trang_thai') == $val ? 'selected' : '' }}>
                        {{ $info['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Lọc</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th>Số PO</th>
                        <th>Nhà cung cấp</th>
                        <th>Ngày đặt</th>
                        <th>Ngày giao DK</th>
                        <th class="text-center">Số mặt hàng</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center no-sort">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $po)
                    @php $info = \App\Models\PurchaseOrder::$trangThaiLabels[$po->trang_thai] ?? ['label'=>$po->trang_thai,'color'=>'secondary']; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $po->so_po }}</td>
                        <td>{{ $po->nhaCungCap?->ten_ncc ?? '—' }}</td>
                        <td>{{ $po->ngay_dat?->format('d/m/Y') }}</td>
                        <td>
                            @if($po->ngay_giao_du_kien)
                                <span class="{{ $po->trang_thai !== 'received' && $po->ngay_giao_du_kien->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $po->ngay_giao_du_kien->format('d/m/Y') }}
                                </span>
                            @else —
                            @endif
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $po->items_count }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $info['color'] }}">{{ $info['label'] }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.purchase-orders.show', $po) }}" class="btn btn-info btn-xs">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if($po->trang_thai !== 'received')
                            <form method="POST" action="{{ route('admin.purchase-orders.destroy', $po) }}"
                                class="d-inline" onsubmit="return confirm('Xóa PO?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted text-center">Chưa có Purchase Order nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $data->links() }}
    </div>
</div>
@endsection
