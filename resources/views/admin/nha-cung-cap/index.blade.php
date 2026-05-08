@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0"><i class="fa-solid fa-truck me-2"></i>Nhà Cung Cấp</h4>
        <a href="{{ route('admin.nha-cung-cap.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>Thêm NCC
        </a>
    </div>
    <div class="card-page">
        @include('admin.partials.alert')
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Tìm Mã / Tên NCC..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th>Mã NCC</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Người LH</th>
                        <th>SĐT</th>
                        <th>Email</th>
                        <th>MST</th>
                        <th class="text-center">Hàng hóa</th>
                        <th class="text-center">PO</th>
                        <th class="text-center no-sort">Trạng thái</th>
                        <th class="text-center no-sort">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->ma_ncc }}</td>
                        <td>{{ $item->ten_ncc }}</td>
                        <td>{{ $item->nguoi_lien_he }}</td>
                        <td>{{ $item->sdt }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->ma_so_thue }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $item->hang_hoa_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">{{ $item->purchase_orders_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $item->active ? 'success' : 'secondary' }}">
                                {{ $item->active ? 'Active' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.nha-cung-cap.edit', $item) }}" class="btn btn-warning btn-xs" title="Sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.nha-cung-cap.destroy', $item) }}"
                                class="d-inline" onsubmit="return confirm('Xóa NCC này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-muted text-center">Chưa có nhà cung cấp nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $data->links() }}
    </div>
</div>
@endsection
