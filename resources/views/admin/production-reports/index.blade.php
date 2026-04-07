@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-industry me-2"></i>Quản lý Báo cáo Sản xuất</h4>
            <a href="{{ route('admin.production-reports.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Báo cáo
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Lệnh SX / Mã NV..." value="{{ request('search') }}">
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
                            <th>Ngày SX</th>
                            <th>Ca</th>
                            <th>Mã NV</th>
                            <th>Lệnh SX</th>
                            <th>Công đoạn</th>
                            <th>Màu</th>
                            <th>Size</th>
                            <th>SL Đạt</th>
                            <th>SL Hư</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->ngay_sx->format('d/m/Y') }}</td>
                                <td>{{ $item->ca }}</td>
                                <td>{{ $item->ma_nv }}</td>
                                <td>{{ $item->lenh_sx }}</td>
                                <td>{{ $item->cong_doan }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ number_format($item->sl_dat, 2) }}</td>
                                <td>{{ number_format($item->sl_hu, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.production-reports.edit', $item) }}"
                                        class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.production-reports.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa báo cáo này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>
@endsection
