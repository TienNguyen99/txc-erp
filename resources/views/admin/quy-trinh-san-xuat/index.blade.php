@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-diagram-project me-2"></i>Quy trình Sản xuất</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Quy trình Sản xuất</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.quy-trinh-san-xuat.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Thêm Quy trình
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-page border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã QT</th>
                            <th>Tên Quy trình</th>
                            <th>Ngày hiệu lực</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quyTrinhs as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->ma_quy_trinh }}</td>
                            <td>{{ $item->ten_quy_trinh }}</td>
                            <td>{{ $item->ngay_hieu_luc ? $item->ngay_hieu_luc->format('d/m/Y') : '' }}</td>
                            <td>
                                @if($item->trang_thai == 'active')
                                    <span class="badge bg-success">Đang sử dụng</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng sử dụng</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.quy-trinh-san-xuat.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.quy-trinh-san-xuat.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
