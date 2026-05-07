@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title"><i class="fa-solid fa-list-ol me-2"></i>Định mức Nguyên vật liệu (BOM)</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-page mb-4">
        <form method="GET" action="{{ route('admin.dinh-muc-nvl.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm Sản phẩm</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Nhập Mã hoặc Tên HH..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-page p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mã HH</th>
                        <th>Tên sản phẩm</th>
                        <th>Nhóm</th>
                        <th>Đơn vị</th>
                        <th class="text-center">Số lượng NVL (BOM)</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $sp)
                        <tr>
                            <td><strong>{{ $sp->ma_hh }}</strong></td>
                            <td>{{ $sp->ten_hh }}</td>
                            <td><span class="badge bg-secondary">{{ $sp->nhom_hh }}</span></td>
                            <td>{{ $sp->don_vi }}</td>
                            <td class="text-center">
                                @if($sp->dinh_muc_nvl_count > 0)
                                    <span class="badge bg-success">{{ $sp->dinh_muc_nvl_count }} mục</span>
                                @else
                                    <span class="badge bg-light text-muted border">Chưa có</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.dinh-muc-nvl.show', $sp->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-gear me-1"></i> Cấu hình BOM
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
