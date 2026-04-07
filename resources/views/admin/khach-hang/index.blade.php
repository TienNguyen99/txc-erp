@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-building me-2"></i>Danh mục Khách hàng</h4>
            <a href="{{ route('admin.khach-hang.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Khách hàng
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Mã KH / Tên KH..." value="{{ request('search') }}">
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
                            <th>Mã KH</th>
                            <th>Tên khách hàng</th>
                            <th>Người liên hệ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>MST</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td class="fw-semibold">{{ $item->ma_kh }}</td>
                                <td>{{ $item->ten_kh }}</td>
                                <td>{{ $item->nguoi_lien_he }}</td>
                                <td>{{ $item->sdt }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->ma_so_thue }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->active ? 'success' : 'secondary' }}">
                                        {{ $item->active ? 'Active' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.khach-hang.edit', $item) }}" class="btn btn-warning btn-xs"><i
                                            class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.khach-hang.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa khách hàng này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>
@endsection
