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
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm PL Number / Màu..." value="{{ request('search') }}">
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
                            <th>Order (Job No)</th>
                            <th>PL Number</th>
                            <th>Size</th>
                            <th>Màu</th>
                            <th>Kích</th>
                            <th>Công đoạn</th>
                            <th>SL Đơn hàng</th>
                            <th>SL Sản xuất</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->order->job_no ?? '—' }}</td>
                                <td>{{ $item->pl_number }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ $item->kich }}</td>
                                <td>{{ $item->cong_doan }}</td>
                                <td>{{ number_format($item->sl_don_hang, 2) }}</td>
                                <td>{{ number_format($item->sl_san_xuat, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.order-tracking.edit', $item) }}"
                                        class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.order-tracking.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa tracking này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>
@endsection
